<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Component\Sync\Queue\Command;

use Symcloud\Component\Sync\Api\ApiInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DownloadCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $childPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var int
     */
    private $size;

    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * DeleteServerCommand constructor.
     *
     * @param string $childPath
     * @param int $size
     * @param ApiInterface $api
     * @param Filesystem $filesystem
     */
    public function __construct($childPath, $size, ApiInterface $api, Filesystem $filesystem)
    {
        $this->childPath = $childPath;
        $this->filesystem = $filesystem;
        $this->size = $size;
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(OutputInterface $output)
    {
        // new server on file
        $path = ROOT_FOLDER . $this->childPath;
        try {
            $this->filesystem->mkdir(dirname($path));

            $this->filesystem->touch($path);
            if ($this->size > 0) {
                $this->api->fileDownload($this->childPath, $path);
            }

            $output->writeln("  -> downloaded\r\n");
        } catch (\Exception $ex) {
            $this->filesystem->remove($path);
            $output->writeln(sprintf("  -> error %s\r\n", $ex->getMessage()));
        }

        return;
    }
}
