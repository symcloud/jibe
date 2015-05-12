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

use Symcloud\Component\Sync\SynchronizerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{
    /**
     * @var SynchronizerInterface
     */
    private $synchronizer;

    /**
     * SyncCommand constructor.
     *
     * @param SynchronizerInterface $synchronizer
     */
    public function __construct($name, SynchronizerInterface $synchronizer)
    {
        parent::__construct($name);

        $this->synchronizer = $synchronizer;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->synchronizer->sync();
    }
}
