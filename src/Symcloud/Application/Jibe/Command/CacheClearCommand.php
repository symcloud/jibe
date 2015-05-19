<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Application\Jibe\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CacheClearCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * ClearCacheCommand constructor.
     *
     * @param string $name
     * @param Filesystem $filesystem
     */
    public function __construct($name, Filesystem $filesystem)
    {
        parent::__construct($name);

        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem->remove(CACHE_FOLDER);
    }
}
