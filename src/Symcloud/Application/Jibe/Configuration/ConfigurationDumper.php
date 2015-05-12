<?php

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
                'parameters' => $this->config
            )
        );

        file_put_contents(PARAMETER_FILE, $yml);
    }
}
