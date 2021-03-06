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

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\Response;
use League\OAuth2\Client\Token\AccessToken;

interface ApiInterface
{
    /**
     * @return array
     */
    public function getReferences();

    /**
     * @param string $path
     * @param int $depth
     *
     * @return array
     */
    public function getDirectory($path = '/', $depth = -1);

    /**
     * @param string $filePath
     *
     * @return RequestInterface
     */
    public function fileUpload($filePath);

    /**
     * @param string $childPath
     * @param string $saveTo
     */
    public function fileDownload($childPath, $saveTo);

    /**
     * @param RequestInterface $request
     *
     * @return Response
     */
    public function send(RequestInterface $request);

    /**
     * @param array $patch
     */
    public function patch($patch);

    /**
     * @param $accessToken
     */
    public function setToken(AccessToken $accessToken);

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl);
}
