<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Application\Jibe\Configuration;

use Symfony\Component\Yaml\Dumper;

class ConfigurationDumper
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Dumper
     */
    private $dumper;

    /**
     * ConfigurationDumper constructor.
     *
     * @param array $config
     * @param Dumper $dumper
     */
    public function __construct(array $config, Dumper $dumper)
    {
        $this->config = $config;
        $this->dumper = $dumper;
    }

    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function dump()
    {
        $yml = $this->dumper->dump(
            array(
                'parameters' => $this->config,
            )
        );

        file_put_contents(PARAMETER_FILE, $yml);
    }
}
