<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\RestBundle\DependencyInjection;

use Easy\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * RestExtension.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class RestExtension extends Extension
{

    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loader->load('rest.yml');
    }

}
