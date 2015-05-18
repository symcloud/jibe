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

class Tree
{
    /**
     * @var array
     */
    private $files;

    /**
     * @var array
     */
    private $tree;

    /**
     * @var array
     */
    private $unmarked;

    public function __construct($tree)
    {
        $this->tree = $tree;
        $this->files = $this->flattenTree($tree);
        $this->unmarked = array_keys($this->files);
    }

    private function flattenTree($item)
    {
        $result = array();
        if ($item !== null &&
            array_key_exists('_embedded', $item) &&
            array_key_exists('children', $item['_embedded'])
        ) {
            foreach ($item['_embedded']['children'] as $key => $child) {
                if ($child !== null) {
                    if (array_key_exists('fileHash', $child)) {
                        $result[$child['path']] = $child;
                    } else {
                        $result = array_merge($this->flattenTree($child), $result);
                    }
                }
            }
        }

        return $result;
    }

    public function append($tree)
    {
        $flatten = $this->flattenTree($tree);
        $this->files = array_merge($this->files, $flatten);
        $this->unmarked = array_merge($this->unmarked, array_keys($flatten));
    }

    public function put($file)
    {
        $parts = explode('/', $file['path']);
        $parts = array_values(array_filter($parts));

        $tree = &$this->tree;
        for ($i = 0; $i < count($parts) - 1; $i++) {
            if (!array_key_exists('_embedded', $tree)) {
                $tree['_embedded'] = array();
            }
            if (!array_key_exists('children', $tree['_embedded'])) {
                $tree['_embedded']['children'] = array();
            }
            if (!array_key_exists($parts[$i], $tree['_embedded']['children'])) {
                $tree['_embedded']['children'][$parts[$i]] = array('_embedded' => array('children' => array()));
            }

            $tree = &$tree['_embedded']['children'][$parts[$i]];
        }

        $tree['_embedded']['children'][$parts[count($parts) - 1]] = $file;
    }

    public function inTree($file)
    {
        return array_key_exists($file, $this->files);
    }

    public function get($file)
    {
        return $this->inTree($file) ? $this->files[$file] : null;
    }

    public function mark($file)
    {
        if (($key = array_search($file, $this->unmarked)) !== false) {
            unset($this->unmarked[$key]);
        }
    }

    public function getUnmarked()
    {
        return $this->unmarked;
    }

    public function getTree()
    {
        return $this->tree;
    }

    public function remove($file)
    {
        $parts = explode('/', $file['path']);
        $parts = array_values(array_filter($parts));

        $tree = &$this->tree;
        for ($i = 0; $i < count($parts) - 1; $i++) {
            if (!array_key_exists('_embedded', $tree)) {
                $tree['_embedded'] = array();
            }
            if (!array_key_exists('children', $tree['_embedded'])) {
                $tree['_embedded']['children'] = array();
            }
            if (!array_key_exists($parts[$i], $tree['_embedded']['children'])) {
                $tree['_embedded']['children'][$parts[$i]] = array('_embedded' => array('children' => array()));
            }

            $tree = &$tree['_embedded']['children'][$parts[$i]];
        }

        unset($tree['_embedded']['children'][$parts[count($parts) - 1]]);
        // TODO remove empty folders
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
}
