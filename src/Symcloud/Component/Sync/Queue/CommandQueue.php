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
use Symcloud\Component\Sync\HashGenerator;
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
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * CommandQueue constructor.
     *
     * @param OutputInterface $output
     * @param ApiInterface $api
     * @param HashGenerator $hashGenerator
     */
    public function __construct(OutputInterface $output, ApiInterface $api, HashGenerator $hashGenerator)
    {
        $this->output = $output;
        $this->api = $api;

        $this->queue = array();
        $this->hashGenerator = $hashGenerator;
    }

    public function upload($file, $childPath)
    {
        $this->enqueue(new UploadCommand($file, $childPath, $this->api, $this->hashGenerator));
    }

    public function enqueue(CommandInterface $command)
    {
        $this->queue[] = $command;
    }

    public function execute($message)
    {
        $patch = array();
        foreach ($this->queue as $command) {
            $patchCommand = $command->execute($this->output);
            if ($patchCommand !== null) {
                $patch[] = $patchCommand;
            }
        }

        if (count($patch) > 0) {
            $patch[] = array(
                'cmd' => 'commit',
                'message' => $message,
            );
            $this->api->patch($patch);
        }
    }
}
