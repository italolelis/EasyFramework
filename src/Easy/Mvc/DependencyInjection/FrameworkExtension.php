<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc\DependencyInjection;

use Easy\HttpKernel\DependencyInjection\Extension;
use Easy\Mvc\Controller\Component\Locale;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * FrameworkExtension.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class FrameworkExtension extends Extension
{

    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loader->load('services.yml');
        $loader->load('web.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        //S\Easy\Utility\Debugger::dump($container);
        if ($config['default_locale']) {

            $locale = new Locale();
            $locale->setLocale($config['default_locale']);
            //\Easy\Utility\Debugger::dump($container);
            $locale->setSession($container->get("session"));

            if ($config['default_timezone']) {
                $locale->setTimezone($config['default_timezone']);
            }

            $container->set('locale', $locale);
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

}
