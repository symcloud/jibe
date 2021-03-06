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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandsCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('jibe.application')) {
            return;
        }

        $definition = $container->getDefinition('jibe.application');
        $taggedServices = $container->findTaggedServiceIds('jibe.command');
        foreach ($taggedServices as $id => $tags) {
            $serviceDefinition = $container->getDefinition($id);
            $definition->addMethodCall(
                'add',
                array(new Reference($id))
            );
            foreach ($tags as $attributes) {
                if (array_key_exists('default', $attributes) && $attributes['default']) {
                    $definition->addMethodCall(
                        'setDefaultCommand',
                        array($serviceDefinition->getArgument(0))
                    );
                }
            }
        }
    }
}
