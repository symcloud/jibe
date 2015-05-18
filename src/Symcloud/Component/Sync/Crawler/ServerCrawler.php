<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Component\Sync\Crawler;

use Symcloud\Component\Sync\Api\ApiInterface;

class ServerCrawler extends BaseCrawler implements CrawlerInterface
{
    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * ServerCrawler constructor.
     *
     * @param ApiInterface $api
     */
    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function run()
    {
        $this->doRun('/');
    }

    private function doRun($directory, $children = null)
    {
        if (!$children) {
            $response = $this->api->getDirectory($directory, 3);
            $children = $response['_embedded']['children'];
        }

        foreach ($children as $name => $child) {
            if (array_key_exists('fileHash', $child)) {
                $this->setFile($child);
            } else {
                $childChildren = array();
                if ($child['hasChildren']) {
                    $childChildren = null;
                    if (sizeof($child['_embedded']['children']) > 0) {
                        $childChildren = $child['_embedded']['children'];
                    }
                }

                $this->doRun($child['path'], $childChildren);
            }
        }
    }
}
