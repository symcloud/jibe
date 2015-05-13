<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Component\Sync;

class HashGenerator
{
    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var string
     */
    private $key;

    /**
     * HashGenerator constructor.
     *
     * @param string $algorithm
     * @param string $key
     */
    public function __construct($algorithm, $key)
    {
        $this->algorithm = $algorithm;
        $this->key = $key;
    }

    public function generateHash($data)
    {
        return hash_hmac($this->algorithm, $data, $this->key);
    }

    public function generateFileHash($filePath)
    {
        return hash_hmac_file($this->algorithm, $filePath, $this->key);
    }
}
