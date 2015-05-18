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
    }

    /**
     * {@inheritdoc}
     */
    public function sync($message)
    {
        $this->output->writeln('Start sync:');

        $this->processFolder(ROOT_FOLDER);

        $this->output->writeln('Finished sync: starting upload changed data');

        $this->commandQueue->execute($message);
    }

    private function getDirectory($path, $depth = null)
    {
        return $this->api->getDirectory($path, $depth)['_embedded']['children'];
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
                    unset($serverFolder[$file]);
                }
            }
        }

        // TODO processClientNotExistingFiles
        // TODO processServerNotExistingFiles
    }

    private function processFile($path, $file, $serverFile = null)
    {
        $filePath = sprintf('%s/%s', $path, $file);
        $this->output->writeln(sprintf('      process file "%s"', $filePath));
        if ($serverFile === null) {
            return $this->commandQueue->upload($filePath);
        }

        $fileHash = $this->hashGenerator->generateFileHash($filePath);
        if ($fileHash === (isset($serverFile['fileHash']) ? $serverFile['fileHash'] : '')) {
            $this->output->writeln('       - not changed');

            return;
        }

        // TODO file changed
    }
}
