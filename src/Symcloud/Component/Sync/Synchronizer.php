<?php

namespace Symcloud\Component\Sync;

use Symcloud\Component\Sync\Api\ApiInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Synchronizer implements SynchronizerInterface
{
    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * Synchronizer constructor.
     * @param ApiInterface $api
     */
    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function sync(OutputInterface $output)
    {
        $x = $this->api->getDirectory();
    }
}
