<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Bundles\MenuBundle\DependencyInjection;

use Easy\Bundles\MenuBundle\EventListener\ViewListener;
use Easy\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * MenuExtension.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class MenuExtension extends Extension
{

    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setParameter("server.requestUri", $_SERVER['REQUEST_URI']);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loader->load('services.yml');

        if ($container->has("event_dispatcher")) {
            $dispatcher = $container->get("event_dispatcher");
            $dispatcher->addSubscriber(new ViewListener());
        }
    }

}
