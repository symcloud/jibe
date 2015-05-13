<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Component\Sync\Queue\Command;

use GuzzleHttp\Event\ProgressEvent;
use Symcloud\Component\Sync\Api\ApiInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class UploadCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * UploadCommand constructor.
     *
     * @param string $filePath
     * @param ApiInterface $api
     */
    public function __construct($filePath, ApiInterface $api)
    {
        $this->filePath = $filePath;
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(OutputInterface $output)
    {
        $fileSize = filesize($this->filePath);
        $progress = new ProgressBar($output);
        $progress->setMessage(sprintf('Upload File %s', $this->filePath));

        $progress->setFormat("%message%\n [%bar%] %percent:3s%% " . Helper::formatMemory($fileSize));
        $progress->start($fileSize);

        $request = $this->api->upload($this->filePath);
        $request->getEmitter()->on(
            'progress',
            function (ProgressEvent $e) use ($progress) {
                $progress->setProgress($e->uploaded);
            }
        );

        $response = $this->api->send($request);
        $progress->finish();
        $output->writeln('');
        $output->writeln('');
        $output->writeln('');

        return json_encode($response->getBody()->getContents(), true)['hash'];
    }
}
