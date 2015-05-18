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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
        $finder = new Finder();
        $finder->files()->in($this->directory);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $path = '/' . $file->getRelativePathname();
            $this->setFile(
                array(
                    'name' => $file->getFilename(),
                    'path' => $path,
                    'fullPath' => $file->getPathname(),
                    'fileHash' => $this->hashGenerator->generateFileHash($file->getPathname()),
                    'version' => array_key_exists($path, $this->cachedFiles) ? $this->cachedFiles[$path]['version'] : null,
                    'oldFileHash' => array_key_exists($path, $this->cachedFiles) ? $this->cachedFiles[$path]['fileHash'] : null,
                    'size' => $file->getSize(),
                )
            );
        }
    }
}
