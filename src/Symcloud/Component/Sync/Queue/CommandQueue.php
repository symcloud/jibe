<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Component\Sync\Queue;

use Symcloud\Component\Sync\Api\ApiInterface;
use Symcloud\Component\Sync\Queue\Command\CommandInterface;
use Symcloud\Component\Sync\Queue\Command\UploadCommand;
use Symfony\Component\Console\Output\OutputInterface;

class CommandQueue implements CommandQueueInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var CommandInterface[]
     */
    private $queue;

    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * CommandQueue constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;

        $this->queue = array();
    }

    public function upload($file)
    {
        $this->enqueue(new UploadCommand($file, $this->api));
    }

    public function enqueue(CommandInterface $command)
    {
        $this->queue[] = $command;
    }

    public function execute()
    {
        foreach ($this->queue as $command) {
            $command->execute($this->output);
        }
    }
}
