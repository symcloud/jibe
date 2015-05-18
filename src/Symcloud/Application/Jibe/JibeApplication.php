<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Application\Jibe;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

class JibeApplication extends Application
{
    public function getDefinition()
    {
        $definition = parent::getDefinition();
        $definition->addOption(new InputOption('debug', 'd', 1, 'Append debug cookie to requests'));

        return $definition;
    }
}
