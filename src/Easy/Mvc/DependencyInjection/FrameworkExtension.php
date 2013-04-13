<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\DependencyInjection;

use Easy\HttpKernel\DependencyInjection\Extension;
use Easy\Mvc\EventListener\ParseListener;
use Easy\Mvc\EventListener\SessionListener;
use Easy\Mvc\EventListener\TemplateListener;
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

        if ($container->has("event_dispatcher")) {
            $dispatcher = $container->get("event_dispatcher");
            $dispatcher->addSubscriber(new ParseListener());
            $dispatcher->addSubscriber(new SessionListener($container));
            $dispatcher->addSubscriber(new TemplateListener($container));
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('kernel.secret', $config['secret']);

        $container->setParameter('kernel.trust_proxy_headers', $config['trust_proxy_headers']);

        $container->setParameter('kernel.default_locale', $config['default_locale']);

        if (isset($config['session'])) {
            $this->registerSessionConfiguration($config['session'], $container, $loader);
        }

        if (isset($config['default_locale'])) {
            $this->registerLocaleConfiguration($config, $container);
        }

        if (isset($config['templating'])) {
            $this->registerTempaltingConfiguration($config['templating'], $container, $loader);
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

    /**
     * Loads the locale configuration.
     * @param array            $config    A session configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    private function registerLocaleConfiguration(array $config, ContainerBuilder $container)
    {
        $locale = $container->get("locale");
        $locale->setLocale($config['default_locale']);

        if (isset($config['session'])) {
            $locale->setSession($container->get("session"));
        }

        if ($config['default_timezone']) {
            $locale->setTimezone($config['default_timezone']);
        }

        $locale->configLocale();
    }

    /**
     * Loads the session configuration.
     *
     * @param array            $config    A session configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param YamlFileLoader    $loader    An YamlFileLoader instance
     */
    private function registerSessionConfiguration(array $config, ContainerBuilder $container, YamlFileLoader $loader)
    {
        $loader->load('session.yml');

        // session storage
        $container->setAlias('session.storage', $config['storage_id']);
        $options = array();
        foreach (array('name', 'cookie_lifetime', 'cookie_path', 'cookie_domain', 'cookie_secure', 'cookie_httponly', 'gc_maxlifetime', 'gc_probability', 'gc_divisor') as $key) {
            if (isset($config[$key])) {
                $options[$key] = $config[$key];
            }
        }

        //we deprecated session options without cookie_ prefix, but we are still supporting them,
        //Let's merge the ones that were supplied without prefix
        foreach (array('lifetime', 'path', 'domain', 'secure', 'httponly') as $key) {
            if (!isset($options['cookie_' . $key]) && isset($config[$key])) {
                $options['cookie_' . $key] = $config[$key];
            }
        }
        $container->setParameter('session.storage.options', $options);

        // session handler (the internal callback registered with PHP session management)
        if (null == $config['handler_id']) {
            // Set the handler class to be null
            $container->getDefinition('session.storage.native')->replaceArgument(1, null);
        } else {
            $container->setAlias('session.handler', $config['handler_id']);
        }

        $container->setParameter('session.save_path', $config['save_path']);

        $this->addClassesToCompile(array(
            'Easy\\Mvc\\EventListener\\SessionListener',
            'Easy\\Storage\\Session\\Storage\\NativeSessionStorage',
            'Easy\\Storage\\Session\\Storage\\Handler\\NativeFileSessionHandler',
            'Easy\\Storage\\Session\\Storage\\Proxy\\AbstractProxy',
            'Easy\\Storage\\Session\\Storage\\Proxy\\SessionHandlerProxy',
            $container->getDefinition('session')->getClass(),
        ));

        if ($container->hasDefinition($config['storage_id'])) {
            $this->addClassesToCompile(array(
                $container->findDefinition('session.storage')->getClass(),
            ));
        }
    }

    /**
     * Loads the templating configuration.
     *
     * @param array            $config    A session configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param YamlFileLoader    $loader    An YamlFileLoader instance
     */
    private function registerTempaltingConfiguration(array $config, ContainerBuilder $container, YamlFileLoader $loader)
    {
        $loader->load('templating.yml');

        $container->register("templating", $config['engines'][0])
                ->addArgument(new \Symfony\Component\DependencyInjection\Reference('template.parser'))
                ->addArgument(new \Symfony\Component\DependencyInjection\Reference('kernel'))
                ->addArgument(new \Symfony\Component\DependencyInjection\Reference('controller.metadata'));
    }

}
