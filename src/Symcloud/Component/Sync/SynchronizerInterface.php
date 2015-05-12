<?php

namespace Symcloud\Component\Sync;

use Symfony\Component\Console\Output\OutputInterface;

interface SynchronizerInterface
{
    public function sync(OutputInterface $output);
}
