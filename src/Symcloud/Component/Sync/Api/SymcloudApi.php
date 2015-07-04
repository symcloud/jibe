<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Component\Sync\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Post\PostFile;
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
     * @var string
     */
    private $baseUrl;
    /**
     * @var
     */
    private $reference;

    /**
     * SymcloudApi constructor.
     *
     * @param Client $client
     * @param AccessToken $token
     * @param ProviderInterface $provider
     * @param string $reference
     */
    public function __construct(Client $client, AccessToken $token, ProviderInterface $provider, $reference)
    {
        $this->client = $client;
        $this->provider = $provider;
        $this->reference = $reference;

        $this->setToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferences()
    {
        $response = $this->client->get((!$this->baseUrl ? '' : rtrim($this->baseUrl, '/')) . '/admin/api/references');
        $data = json_decode($response->getBody()->getContents(), true);

        return $data['_embedded']['references'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectory($path = '/', $depth = -1)
    {
        $path = ($path !== './') ? ('/' . ltrim($path, '/')) : '';
        $response = $this->client->get(rtrim('/admin/api/directory/' . $this->reference . $path, '/'));

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function fileUpload($filePath)
    {
        $request = $this->client->createRequest(
            'POST',
            '/admin/api/chunks',
            array('body' => array('chunk-file' => new PostFile('chunk-file', fopen($filePath, 'r'))))
        );

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function fileDownload($childPath, $saveTo)
    {
        return $this->client->get(
            '/admin/api/file/' . $this->reference . '/' . ltrim($childPath, '/') . '?content',
            array('save_to' => $saveTo)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function patch($patch)
    {
        return $this->client->patch(
            '/admin/api/references/' . $this->reference,
            array('body' => array('commands' => $patch))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request)
    {
        return $this->client->send($request);
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(AccessToken $accessToken)
    {
        $this->token = $accessToken;
        $this->client->setDefaultOption('headers', $this->provider->getHeaders($this->token));
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }
}
