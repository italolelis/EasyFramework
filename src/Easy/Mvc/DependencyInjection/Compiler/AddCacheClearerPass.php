<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers the cache clearers.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class AddCacheClearerPass implements CompilerPassInterface
{

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cache_clearer')) {
            return;
        }

        $clearers = array();
        foreach ($container->findTaggedServiceIds('kernel.cache_clearer') as $id => $attributes) {
            $clearers[] = new Reference($id);
        }

        $container->getDefinition('cache_clearer')->replaceArgument(0, $clearers);
    }

}