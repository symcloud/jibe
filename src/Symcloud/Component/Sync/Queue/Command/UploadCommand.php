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

class UploadCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * UploadCommand constructor.
     *
     * @param string $file
     * @param ApiInterface $api
     */
    public function __construct($file, ApiInterface $api)
    {
        $this->file = $file;
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(OutputInterface $output)
    {
        // TODO: Implement execute() method.
    }
}
