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
use WebSocket\Client;

class BlobUploadCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client('ws://localhost:9876/blob');
        $handle = fopen(ROOT_FOLDER . '/Demo PDF - Alice in Wonderland.pdf', 'r');

        while (!feof($handle)) {
            $buffer = fread($handle, 200000);

            if ($buffer !== false) {
                $client->send(base64_encode($buffer));
            }
        }
    }
}
