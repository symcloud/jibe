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

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DeleteLocalCommand implements CommandInterface
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
     * DeleteServerCommand constructor.
     *
     * @param string $childPath
     * @param Filesystem $filesystem
     */
    public function __construct($childPath, Filesystem $filesystem)
    {
        $this->childPath = $childPath;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(OutputInterface $output)
    {
        $this->filesystem->remove(ROOT_FOLDER . '/' . ltrim($this->childPath, '/'));
    }
}
