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

use Symfony\Component\Console\Output\OutputInterface;

interface SynchronizerInterface
{
    public function sync(OutputInterface $output, $message);
}
