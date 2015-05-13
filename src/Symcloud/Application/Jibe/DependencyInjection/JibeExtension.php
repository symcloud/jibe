<?php

/*
 * This file is part of the Symcloud Distributed-Storage.
 *
 * (c) Symcloud and Johannes Wachter
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symcloud\Application\Jibe\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class JibeExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = array(
            'server' => null,
            'client' => null,
            'hash-algorithm' => null,
            'hash-key' => null,
            'access-token' => array(
                'access_token' => '',
            ),
        );
        if ($container->hasParameter('server')) {
            $config['server'] = $container->getParameter('server');
        }
        if ($container->hasParameter('client')) {
            $config['client'] = $container->getParameter('client');
        }
        if ($container->hasParameter('access-token')) {
            $config['access-token'] = $container->getParameter('access-token');
        }
        if ($container->hasParameter('hash-algorithm')) {
            $config['hash-algorithm'] = $container->getParameter('hash-algorithm');
        }
        if ($container->hasParameter('hash-key')) {
            $config['hash-key'] = $container->getParameter('hash-key');
        }
        $container->setParameter('jibe.server', $config['server']);
        $container->setParameter('jibe.client', $config['client']);
        $container->setParameter('jibe.hash-key', $config['hash-key']);
        $container->setParameter('jibe.hash-algorithm', $config['hash-algorithm']);
        $container->setParameter('jibe.access-token', $config['access-token']);

        $container->setParameter('jibe.configuration', $config);

        $locator = new FileLocator(__DIR__ . '/../Resources/config');

        $xmlLoader = new XmlFileLoader($container, $locator);
        $xmlLoader->load('services.xml');
    }
}
