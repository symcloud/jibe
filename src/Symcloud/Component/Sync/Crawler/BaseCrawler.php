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

abstract class BaseCrawler implements CrawlerInterface
{
    protected $files = array();

    public function setFile($file)
    {
        unset($file['_links']);
        unset($file['_embedded']);

        $path = $file['path'];

        if (array_key_exists($path, $this->files)) {
            $this->files[$path] = array_merge($this->files[$path], $file);
        } else {
            $this->files[$path] = $file;
        }
    }

    public function touchFile($path)
    {
        $this->files[$path] = array(
            'path' => $path,
            'version' => 0,
            'fileHash' => null,
        );
    }

    public function removeFile($path)
    {
        if (array_key_exists($path, $this->files)) {
            unset($this->files[$path]);
        }
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getFile($path)
    {
        if (!array_key_exists($path, $this->files)) {
            return;
        }

        return $this->files[$path];
    }
}
