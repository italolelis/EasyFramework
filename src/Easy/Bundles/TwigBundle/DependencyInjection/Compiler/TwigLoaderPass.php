<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\TwigBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds services tagged twig.loader as Twig loaders
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class TwigLoaderPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('twig')) {
            return;
        }

        // register additional template loaders
        $loaderIds = $container->findTaggedServiceIds('twig.loader');

        if (count($loaderIds) === 0) {
            throw new LogicException('No twig loaders found. You need to tag at least one loader with "twig.loader"');
        }

        if (count($loaderIds) === 1) {
            $container->setAlias('twig.loader', key($loaderIds));
        } else {
            $chainLoader = $container->getDefinition('twig.loader.chain');
            foreach (array_keys($loaderIds) as $id) {
                $chainLoader->addMethodCall('addLoader', array(new Reference($id)));
            }
            $container->setAlias('twig.loader', 'twig.loader.chain');
        }
    }

}