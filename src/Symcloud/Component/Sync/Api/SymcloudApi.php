<?php

namespace Symcloud\Component\Sync\Api;

use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\ProviderInterface;
use League\OAuth2\Client\Token\AccessToken;

class SymcloudApi implements ApiInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var AccessToken
     */
    private $token;

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * SymcloudApi constructor.
     * @param Client $client
     * @param AccessToken $token
     * @param ProviderInterface $provider
     */
    public function __construct(Client $client, AccessToken $token, ProviderInterface $provider)
    {
        $this->client = $client;
        $this->token = $token;
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectory($path = '/', $depth = -1)
    {
        $path = ($path !== '/') ? ('/' . ltrim($path, '/')) : '';
        $response = $this->client->get('/admin/api/directory' . $path, array('headers' => $this->provider->getHeaders($this->token)));

        return json_decode($response->getBody()->getContents(), true);
    }
}
