<?php

namespace Symcloud\Application\Jibe\Command;

use League\OAuth2\Client\Provider\ProviderInterface;
use Symcloud\Application\Jibe\Configuration\ConfigurationDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshTokenCommand extends Command
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var ConfigurationDumper
     */
    private $dumper;

    /**
     * RefreshTokenCommand constructor.
     * @param null|string $name
     * @param array $config
     * @param ConfigurationDumper $dumper
     * @param ProviderInterface $provider
     */
    public function __construct($name, array $config, ConfigurationDumper $dumper, ProviderInterface $provider)
    {
        parent::__construct($name);

        $this->config = $config;
        $this->provider = $provider;
        $this->dumper = $dumper;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accessToken = $this->provider->getAccessToken(
            'refresh_token',
            array('refresh_token' => $this->config['access-token']['refresh_token'])
        );

        $this->dumper->setConfig(
            'access-token',
            array(
                'token' => $accessToken->accessToken,
                'refresh_token' => $accessToken->refreshToken,
                'expires' => $accessToken->expires,
                'uid' => $accessToken->uid
            )
        );

        $this->dumper->dump();
    }
}
