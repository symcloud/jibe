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
use GuzzleHttp\Exception\ClientException;
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
     * SymcloudApi constructor.
     *
     * @param Client $client
     * @param AccessToken $token
     * @param ProviderInterface $provider
     */
    public function __construct(Client $client, AccessToken $token, ProviderInterface $provider)
    {
        $this->client = $client;
        $this->token = $token;
        $this->provider = $provider;

        $this->client->setDefaultOption('headers', $this->provider->getHeaders($this->token));
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectory($path = '/', $depth = -1)
    {
        $path = ($path !== './') ? ('/' . ltrim($path, '/')) : '';
        $response = $this->client->get(rtrim('/admin/api/directory' . $path, '/'));

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function fileUpload($filePath)
    {
        $request = $this->client->createRequest(
            'POST',
            '/admin/api/blobs',
            array('body' => array('blob-file' => new PostFile('blob-file', fopen($filePath, 'r'))))
        );

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function fileExists($hash)
    {
        try {
            $response = $this->client->head('/admin/api/blobs/' . $hash);
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        }

        return $response->getStatusCode() === 200;
    }

    /**
     * {@inheritdoc}
     */
    public function fileDownload($childPath, $saveTo)
    {
        return $this->client->get('/admin/api/file/' . ltrim($childPath, '/') . '?content', array('save_to' => $saveTo));
    }

    /**
     * {@inheritdoc}
     */
    public function patch($patch)
    {
        return $this->client->patch('/admin/api/files', array('body' => array('commands' => $patch)));
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request)
    {
        return $this->client->send($request);
    }
}
