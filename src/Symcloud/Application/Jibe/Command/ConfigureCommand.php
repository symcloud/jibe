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

use League\OAuth2\Client\Provider\ProviderInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symcloud\Application\Jibe\Configuration\ConfigurationDumper;
use Symcloud\Component\Sync\Api\ApiInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ConfigureCommand extends Command
{
    /**
     * FIXME utilize? or use symfony validation.
     */
    const PATTERN = '~^
            (%s)://                                 # protocol
            (([\pL\pN-]+:)?([\pL\pN-]+)@)?          # basic auth
            (
                ([\pL\pN\pS-\.])+(\.?([\pL]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                              # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                 # a IP address
                    |                                              # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # a IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (/?|/\S+)                               # a /, nothing or a / with something
        $~ixu';

    /**
     * @var array
     */
    private $config;

    /**
     * @var ConfigurationDumper
     */
    private $dumper;

    /**
     * @var ProviderInterface
     */
    private $provider;
    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * ConfigureCommand constructor.
     *
     * @param string $name
     * @param array $config
     * @param ConfigurationDumper $dumper
     * @param ProviderInterface $provider
     * @param ApiInterface $api
     */
    public function __construct(
        $name,
        array $config,
        ConfigurationDumper $dumper,
        ProviderInterface $provider,
        ApiInterface $api
    ) {
        parent::__construct($name);

        $this->config = $config;
        $this->dumper = $dumper;
        $this->provider = $provider;
        $this->api = $api;
    }

    protected function configure()
    {
        $this->addOption('server', 's', InputOption::VALUE_OPTIONAL)
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL)
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL)
            ->addOption('client-id', null, InputOption::VALUE_OPTIONAL)
            ->addOption('client-secret', null, InputOption::VALUE_OPTIONAL)
            ->addOption('hash-algorithm', null, InputOption::VALUE_REQUIRED, '', 'md5')
            ->addOption('hash-key', null, InputOption::VALUE_REQUIRED, '', 'ThisTokenIsNotSoSecretChangeIt');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $urlPattern = sprintf(static::PATTERN, implode('|', array('http', 'https')));

        if (!$input->getOption('server')) {
            $question = new Question(
                'Server base URL' . ($this->config['server'] !== null ? ' [' . $this->config['server'] . ']' : '') . ': ',
                $this->config['server']
            );
            $question->setValidator(
                function ($url) use ($urlPattern) {
                    if (!preg_match($urlPattern, $url)) {
                        throw new \InvalidArgumentException(sprintf('URL "%s" is not valid', $url));
                    }

                    return $url;
                }
            );
            $server = $helper->ask($input, $output, $question);
            $input->setOption('server', $server);
        }

        if (!$input->getOption('client-id')) {
            $question = new Question(
                'Client-ID' . ($this->config['client']['id'] !== null ? ' [' . $this->config['client']['id'] . ']' : '') . ': ',
                $this->config['client']['id']
            );
            $clientId = $helper->ask($input, $output, $question);
            $input->setOption('client-id', $clientId);
        }

        if (!$input->getOption('client-secret')) {
            $question = new Question(
                'Client-Secret: ',
                $this->config['client']['secret']
            );
            $secret = $helper->ask($input, $output, $question);
            $input->setOption('client-secret', $secret);
        }

        if (!$input->getOption('username')) {
            $question = new Question(
                'Username: '
            );
            $userName = $helper->ask($input, $output, $question);
            $input->setOption('username', $userName);
        }

        if (!$input->getOption('password')) {
            $question = new Question(
                'Password: '
            );
            $question->setHidden(true);
            $password = $helper->ask($input, $output, $question);
            $input->setOption('password', $password);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->provider->setOptions(
            array(
                'clientId' => $input->getOption('client-id'),
                'clientSecret' => $input->getOption('client-secret'),
                'scopes' => ['files'],
                'server' => rtrim($input->getOption('server'), '/') . '/',
            )
        );

        /** @var AccessToken $accessToken */
        $accessToken = $this->provider->getAccessToken(
            'password',
            array(
                'username' => $input->getOption('username'),
                'password' => $input->getOption('password'),
            )
        );

        $helper = $this->getHelper('question');
        $this->api->setToken($accessToken);
        $this->api->setBaseUrl(rtrim($input->getOption('server'), '/') . '/');

        $references = $this->api->getReferences();
        $data = array_map(
            function ($reference) {
                return $reference['name'];
            },
            $references
        );

        $question = new ChoiceQuestion('Which reference you want to choose?', $data);
        $referenceName = $helper->ask($input, $output, $question);

        $data = array_filter(
            $references,
            function ($reference) use ($referenceName) {
                return $reference['name'] === $referenceName;
            }
        );

        $this->dumper->setConfig('server', rtrim($input->getOption('server'), '/') . '/');
        $this->dumper->setConfig('reference', $data[0]['hash']);
        $this->dumper->setConfig(
            'client',
            array(
                'id' => $input->getOption('client-id'),
                'secret' => $input->getOption('client-secret'),
            )
        );
        $this->dumper->setConfig(
            'access-token',
            array(
                'access_token' => $accessToken->accessToken,
                'refresh_token' => $accessToken->refreshToken,
                'expires' => $accessToken->expires,
                'uid' => $accessToken->uid,
            )
        );

        $this->dumper->setConfig('hash-algorithm', $input->getOption('hash-algorithm'));
        $this->dumper->setConfig('hash-key', $input->getOption('hash-key'));

        $this->dumper->dump();
    }
}
