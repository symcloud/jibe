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

use Symcloud\Component\Sync\HashGenerator;

class DirectoryCrawler extends BaseCrawler implements CrawlerInterface
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @var
     */
    private $cachedFiles;

    /**
     * DirectoryCrawler constructor.
     *
     * @param string $directory
     * @param HashGenerator $hashGenerator
     * @param $cachedFiles
     */
    public function __construct($directory, HashGenerator $hashGenerator, $cachedFiles)
    {
        $this->directory = $directory;
        $this->hashGenerator = $hashGenerator;
        $this->cachedFiles = $cachedFiles;
    }

    public function run()
    {
        $this->doRun();
    }

    private function doRun($path = '/')
    {
        $parentPath = rtrim($this->directory . '/' . $path, '/');
        foreach (scandir($parentPath) as $child) {
            if (strpos($child, '.') !== 0) {
                $fullPath = rtrim($parentPath, '/') . '/' . ltrim($child, '/');
                $childPath = rtrim(ltrim($path . '/' . $child, '/'), '/');

                if (is_file($fullPath)) {
                    $this->append($childPath);
                } else {
                    $this->doRun($childPath);
                }
            }
        }
    }

    private function append($path)
    {
        $path = '/' . $path;
        $fullPath = $this->directory . $path;

        $this->setFile(
            array(
                'name' => basename($path),
                'path' => $path,
                'fullPath' => $fullPath,
                'fileHash' => $this->hashGenerator->generateFileHash($fullPath),
                'version' => array_key_exists($path, $this->cachedFiles) ? $this->cachedFiles[$path]['version'] : null,
                'oldFileHash' => array_key_exists(
                    $path,
                    $this->cachedFiles
                ) ? $this->cachedFiles[$path]['fileHash'] : null,
                'size' => filesize($fullPath),
            )
        );
    }
}
