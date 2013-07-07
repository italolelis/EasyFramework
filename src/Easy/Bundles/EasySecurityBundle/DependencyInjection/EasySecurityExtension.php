<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\EasySecurityBundle\DependencyInjection;

use Easy\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * FrameworkExtension.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class EasySecurityExtension extends Extension
{

    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs = $configs[0];

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loader->load('security.yml');

        $container->setParameter('security.configs', $configs);

        if (isset($configs['encoders'])) {
            $this->registerEncoderConfiguration($configs, $container);
        }

        $this->registerProviderConfiguration($configs, $container);
    }

    public function registerEncoderConfiguration($configs, ContainerBuilder $container)
    {
        $encoders = $configs['encoders'];

        foreach ($encoders as $id => $enconder) {
            $container->set("hash.engine", new $enconder());
        }
    }

    public function registerProviderConfiguration($configs, ContainerBuilder $container)
    {
        $providers = $configs['providers'];
        if (isset($providers['dao'])) {
            $this->registerDaoProviderConfiguration($providers['dao'], $container);
            $firewall = $configs['firewalls'];
            $this->registerDaoFirewallConfiguration($firewall, $container);
        }
    }

    public function registerDaoProviderConfiguration($configs, ContainerBuilder $container)
    {
        //set an alias to dao.provider
        $container->setAlias('auth', 'dao.provider');

        //auth properties
        $container->setParameter('auth.default', 'dao.provider');
        $container->setParameter('model.name', $configs["model"]);
        $container->setParameter('model.properties', $configs["model_properties"]);
        $container->setParameter('model.fields', $configs["model_fields"]);

        //acl properties
        $container->setParameter('model.username', $configs["model_fields"]["username"]);
    }

    public function registerDaoFirewallConfiguration($configs, ContainerBuilder $container)
    {
        if (isset($configs["secured_area"])) {
            $secured_area = $configs["secured_area"];

            if (isset($secured_area['login'])) {
                $login = $secured_area['login'];
                $container->setParameter('acl.login_redirect', $login["login_path"]);
                $container->setParameter('acl.login_action', $login["check_path"]);

                if (isset($login['error_message'])) {
                    $container->setParameter('acl.login_error', $login["error_message"]);
                }
            }

            if (isset($secured_area['logout'])) {
                $logout = $secured_area['logout'];
                $container->setParameter('acl.logout_redirect', $logout['path']);
            }
        }
    }

}

