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

class DeleteCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $childPath;

    /**
     * DeleteCommand constructor.
     *
     * @param string $childPath
     */
    public function __construct($childPath)
    {
        $this->childPath = $childPath;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(OutputInterface $output)
    {
        return array(
            'command' => 'delete',
            'path' => $this->childPath,
        );
    }
}
