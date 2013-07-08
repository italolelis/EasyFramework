<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\RestBundle\DependencyInjection;

use Easy\Bundles\RestBundle\EventListener\RestListener;
use Easy\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        if ($container->has("event_dispatcher")) {
            $dispatcher = $container->get("event_dispatcher");
            $dispatcher->addSubscriber(new RestListener());
        }
    }

}
