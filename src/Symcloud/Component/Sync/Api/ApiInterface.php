<?php

namespace Symcloud\Component\Sync\Api;

interface ApiInterface
{
    /**
     * @param string $path
     * @param int $depth
     * @return array
     */
    public function getDirectory($path = '/', $depth = -1);
}
