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

use Symcloud\Component\Sync\Queue\Command\CommandInterface;

interface CommandQueueInterface
{
    public function upload($file);

    public function deleteServer($file);

    public function deleteLocal($file);

    public function download($file, $size);

    public function enqueue(CommandInterface $command);

    public function execute($message);
}
