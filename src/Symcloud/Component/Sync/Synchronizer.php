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
use Symcloud\Component\Sync\Crawler\CrawlerInterface;
use Symcloud\Component\Sync\Crawler\DirectoryCrawler;
use Symcloud\Component\Sync\Crawler\ServerCrawler;
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
        $cacheId = 'local-files';
        $files = array();
        if ($this->cache->contains($cacheId)) {
            $files = $this->cache->fetch($cacheId);
        }

        $localCrawler = new DirectoryCrawler(ROOT_FOLDER, $this->hashGenerator, $files);
        $serverCrawler = new ServerCrawler($this->api);
        $localCrawler->run();
        $serverCrawler->run();

        $this->compare($serverCrawler, $localCrawler);

        $this->commandQueue->execute($message);

        $this->cache->save($cacheId, $localCrawler->getFiles());
    }

    private function compare(CrawlerInterface $server, CrawlerInterface $local)
    {
        $diff = array_diff(array_keys($local->getFiles()), array_keys($server->getFiles()));
        foreach ($diff as $path) {
            $server->touchFile($path);
        }

        foreach ($server->getFiles() as $path => $serverFile) {
            $localFile = $local->getFile($path);

            if ($this->isUpload($serverFile, $localFile)) {
                $this->commandQueue->upload($localFile['fullPath']);
            } elseif ($this->isDownload($serverFile, $localFile)) {
                $this->commandQueue->download($path, $serverFile['size']);
            } elseif ($this->isDeleteLocal($serverFile, $localFile)) {
                $this->commandQueue->deleteLocal($path);
            } elseif ($this->isDeleteServer($serverFile, $localFile)) {
                $this->commandQueue->deleteServer($path);
            } elseif ($this->isConflict($serverFile, $localFile)) {
                // TODO conflict
                $this->output->writeln('<error>File conflict "' . $path . '"</error>');
            }

            if (!$serverFile) {
                $local->removeFile($path);
            } else {
                $local->setFile($serverFile);
            }
        }
    }

    private function isUpload($serverFile, $localFile)
    {
        if (// case 6
            ($serverFile === null && $localFile['version'] === null) ||
            // case 3
            ($localFile['fileHash'] !== $serverFile['fileHash'] && $localFile['version'] === $serverFile['version'])
        ) {
            return true;
        }

        return false;
    }

    public function isDownload($serverFile, $localFile)
    {
        if (// case 7
            ($localFile === null) ||
            // case 2
            ($localFile['fileHash'] !== $serverFile['fileHash'] && $localFile['version'] < $serverFile['version'])
        ) {
            return true;
        }

        return false;
    }

    public function isDeleteLocal($serverFile, $localFile)
    {
        // case 8
        if ($serverFile === null) {
            return true;
        }

        return false;
    }

    private function isDeleteServer($serverFile, $localFile)
    {
        // case 9
        if ($localFile['fileHash'] === null && $localFile['version'] === $serverFile['version']) {
            return true;
        }

        return false;
    }

    private function isConflict($serverFile, $localFile)
    {
        // case 4
        if ($localFile['oldFileHash'] !== $localFile['fileHash'] &&
            $localFile['version'] < $serverFile['version'] &&
            $localFile['oldFileHash'] !== $serverFile['fileHash']
        ) {
            return true;
        }

        return false;
    }
}
