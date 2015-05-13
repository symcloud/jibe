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
     * Synchronizer constructor.
     *
     * @param ApiInterface $api
     * @param Filesystem $filesystem
     * @param CommandQueueInterface $commandQueue
     */
    public function __construct(ApiInterface $api, Filesystem $filesystem, CommandQueueInterface $commandQueue)
    {
        $this->api = $api;
        $this->filesystem = $filesystem;
        $this->commandQueue = $commandQueue;
    }

    /**
     * {@inheritdoc}
     */
    public function sync(OutputInterface $output, $message)
    {
        $this->processFolder(ROOT_FOLDER);
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
        if ($serverFile === null) {
            return $this->commandQueue->upload($filePath, $this->filesystem->makePathRelative($filePath, ROOT_FOLDER));
        }

        $fileHash = md5_file($filePath);
        if ($fileHash === (isset($serverFile['fileHash']) ? $serverFile['fileHash'] : '')) {
            return;
        }

        // TODO file changed
    }
}
