<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Application\Jibe\Command;

use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command
{
    /**
     * @var AccessToken
     */
    private $token;

    /**
     * StatusCommand constructor.
     *
     * @param null|string $name
     * @param AccessToken $token
     */
    public function __construct($name, AccessToken $token)
    {
        parent::__construct($name);

        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logo = <<<EOF
       ___                     ___           ___
      /\  \        ___        /\  \         /\  \
      \:\  \      /\  \      /::\  \       /::\  \
  ___ /::\__\     \:\  \    /:/\:\  \     /:/\:\  \
 /\  /:/\/__/     /::\__\  /::\~\:\__\   /::\~\:\  \
 \:\/:/  /     __/:/\/__/ /:/\:\ \:|__| /:/\:\ \:\__\
  \::/  /     /\/:/  /    \:\~\:\/:/  / \:\~\:\ \/__/
   \/__/      \::/__/      \:\ \::/  /   \:\ \:\__\
               \:\__\       \:\/:/  /     \:\ \/__/
                \/__/        \::/__/       \:\__\
                              ~~            \/__/
EOF;
        $output->writeln($logo);

        $output->write('Token-Status: ');

        if ($this->token->accessToken === '') {
            $output->writeln('<error>Not configured</error>');
        } elseif (time() > $this->token->expires) {
            $output->writeln('<comment>Expired</comment>');
        } else {
            $output->writeln('<info>OK</info>');
        }
    }
}
