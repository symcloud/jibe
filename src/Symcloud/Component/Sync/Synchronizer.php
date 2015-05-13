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
     * Synchronizer constructor.
     *
     * @param ApiInterface $api
     */
    public function __construct(ApiInterface $api)
    {
        $this->api = $api;

        // TODO inject
        $this->filesystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function sync(OutputInterface $output)
    {
        $queue = new Queue\CommandQueue($output);

        $this->processFolder(ROOT_FOLDER, $queue);
        $queue->execute();
    }

    private function getDirectory($path, $depth = null)
    {
        return $this->api->getDirectory($path, $depth)['_embedded']['children'];
    }

    private function processFolder($path, CommandQueueInterface $queue, $serverFolder = null)
    {
        if (!$serverFolder) {
            $serverFolder = $this->getDirectory($this->filesystem->makePathRelative($path, ROOT_FOLDER));
        }

        foreach (scandir($path) as $file) {
            if ($file !== '..' && $file !== '.' && $file !== '.symcloud' && strpos($file, '.') !== 0) {
                $childPath = $path . '/' . $file;
                if (is_file($childPath)) {
                    $this->processFile($path, $file, $queue, $serverFolder[$file] ?: null);
                    unset($serverFolder[$file]);
                }
            }
        }

        // TODO processClientNotExistingFiles
        // TODO processServerNotExistingFiles
    }

    private function processFile($path, $file, CommandQueueInterface $queue, $serverFile = null)
    {
        $filePath = sprintf('%s/%s', $path, $file);
        if ($serverFile === null) {
            return $queue->upload($filePath);
        }

        $fileHash = md5_file($filePath);
        if ($fileHash === (isset($serverFile['fileHash']) ? $serverFile['fileHash'] : '')) {
            return;
        }

        // TODO file changed
    }
}
