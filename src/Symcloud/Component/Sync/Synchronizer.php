<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Component\Sync;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Symcloud\Component\Sync\Api\ApiInterface;
use Symcloud\Component\Sync\Queue\CommandQueueInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Synchronizer implements SynchronizerInterface
{
    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CommandQueueInterface
     */
    private $commandQueue;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @var Tree
     */
    private $clientTree;

    /**
     * @var Tree
     */
    private $serverTree;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * Synchronizer constructor.
     *
     * @param ApiInterface $api
     * @param Filesystem $filesystem
     * @param CommandQueueInterface $commandQueue
     * @param HashGenerator $hashGenerator
     * @param OutputInterface $output
     */
    public function __construct(
        ApiInterface $api,
        Filesystem $filesystem,
        CommandQueueInterface $commandQueue,
        HashGenerator $hashGenerator,
        OutputInterface $output
    ) {
        $this->api = $api;
        $this->filesystem = $filesystem;
        $this->commandQueue = $commandQueue;
        $this->hashGenerator = $hashGenerator;
        $this->output = $output;

        $this->cache = new FilesystemCache(CACHE_FOLDER);
    }

    /**
     * {@inheritdoc}
     */
    public function sync($message)
    {
        $treeData = array();
        if ($this->cache->contains('client-tree')) {
            $treeData = $this->cache->fetch('client-tree');
        }

        $this->clientTree = new Tree($treeData);
        $this->serverTree = new Tree(array());

        $this->output->writeln('Start sync:');
        $this->processFolder(ROOT_FOLDER);

        $this->processClientNotExistingFiles($this->serverTree->getUnmarked());
        $this->processServerNotExistingFiles($this->clientTree->getUnmarked());

        $this->output->writeln('Finished sync; starting upload changed data');
        $this->output->writeln('');
        $this->commandQueue->execute($message);

        $this->cache->save('client-tree', $this->clientTree->getTree());
    }

    private function getDirectory($path, $depth = null)
    {
        $response = $this->api->getDirectory($path, $depth);
        $this->serverTree->append($response);

        return $response['_embedded']['children'];
    }

    private function processFolder($path, $serverFolder = null)
    {
        if (!$serverFolder) {
            $serverFolder = $this->getDirectory($this->filesystem->makePathRelative($path, ROOT_FOLDER));
        }
        $this->output->writeln(sprintf('   process folder "%s"', $path));

        foreach (scandir($path) as $file) {
            if ($file !== '..' && $file !== '.' && $file !== '.symcloud' && strpos($file, '.') !== 0) {
                $childPath = $path . '/' . $file;
                if (is_file($childPath)) {
                    $serverFile = isset($serverFolder[$file]) ? $serverFolder[$file] : null;
                    $this->processFile($path, $file, $serverFile);
                }
            }
        }

        // TODO processClientNotExistingFiles
        // TODO processServerNotExistingFiles
    }

    private function processFile($path, $file, $serverFile = null)
    {
        $filePath = sprintf('%s/%s', $path, $file);
        $relativePath = '/' . rtrim($this->filesystem->makePathRelative($filePath, ROOT_FOLDER), '/');
        $this->output->writeln(sprintf('      process file "%s"', $filePath));
        if ($serverFile === null) {
            // TODO perhaps deleted on server?
            return $this->processNewFile($file, $filePath, $relativePath);
        }

        $fileHash = $this->hashGenerator->generateFileHash($filePath);
        if ($fileHash === (isset($serverFile['fileHash']) ? $serverFile['fileHash'] : '')) {
            $this->output->writeln('       - not changed');
            $this->clientTree->put($serverFile);
            $this->clientTree->mark($relativePath);
            $this->serverTree->mark($relativePath);

            return;
        }

        $this->processChangedFile($filePath, $fileHash, $serverFile);
    }

    private function processNewFile($file, $filePath, $relativePath)
    {
        $this->clientTree->put(
            array(
                'name' => $file,
                'path' => $relativePath,
                'version' => 1,
                'fileHash' => $this->hashGenerator->generateFileHash($filePath),
            )
        );
        $this->clientTree->mark($relativePath);
        $this->serverTree->mark($relativePath);

        return $this->commandQueue->upload($filePath);
    }

    private function processChangedFile($filePath, $fileHash, $serverFile)
    {
        $this->output->writeln('       - changed');
        $clientFile = $this->clientTree->get($serverFile['path']);

        $clientFileChanged = ($fileHash !== (isset($clientFile['fileHash']) ? $clientFile['fileHash'] : ''));
        $clientVersion = $clientFile['version'];
        $serverVersion = $serverFile['version'];

        // TODO possibilities
        // * server deleted, client no change
        // * server version > client version, client changed

        if ($clientFileChanged && version_compare($clientVersion, $serverVersion, '=')) {
            // upload new version server file doesn`t have changed
            $this->commandQueue->upload($filePath);

            $serverFile['version'] = $serverFile['version']++;
            $serverFile['fileHash'] = $fileHash;

            $this->clientTree->put($serverFile);
            $this->serverTree->mark($serverFile['path']);
            $this->clientTree->mark($serverFile['path']);
        }
    }

    private function processClientNotExistingFiles($files)
    {
        foreach ($files as $filePath) {
            $this->processNotExistingFile(
                $this->serverTree->get($filePath),
                $this->clientTree->get($filePath)
            );
        }
    }

    private function processNotExistingFile($serverFile, $clientFile)
    {
        // TODO server file is deleted -> return

        // check if file is deleted
        $clientFileExists = $clientFile !== null;
        $clientVersion = $clientFileExists ? $clientFile['version'] : -1;
        $serverVersion = $serverFile['version'];

        echo(sprintf("Not existing file: %s\r\n", $serverFile['path']));

        if ($clientFileExists && $clientVersion == $serverVersion) {
            // if it exists in client-tree and the version equals => delete on server
            $this->commandQueue->delete($serverFile['path']);

            $this->clientTree->remove($serverFile);
            $this->clientTree->mark($serverFile['path']);
            $this->serverTree->mark($serverFile['path']);

            echo("  -> deleted\r\n");
        } elseif (!$clientFileExists) {
            $this->commandQueue->download($serverFile['path'], $serverFile['size']);

            $this->clientTree->put($serverFile);
            $this->clientTree->mark($serverFile['path']);
            $this->serverTree->mark($serverFile['path']);
        } else {
            // TODO conflict
            // server version changed
            // copy server file to an own file and rename it with prefix (like "deleted by xxx - *")
            echo("  -> conflict\r\n");
        }
    }

    private function processServerNotExistingFiles($files)
    {
        // currently conflicted files (they will not be handled / marked)
        // when will be called this

        foreach ($files as $filePath) {
            echo(sprintf("Not existing file on server: %s\r\n", $filePath));
        }
    }
}
