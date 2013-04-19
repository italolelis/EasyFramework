<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SecurityBundle\DependencyInjection;

use Easy\Bundles\SecurityBundle\EventListener\AuthorizationListener;
use Easy\Bundles\SecurityBundle\EventListener\DaoAuthenticationListener;
use Easy\HttpKernel\DependencyInjection\Extension;
use Easy\Security\Authentication\AuthenticationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * FrameworkExtension.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class SecurityExtension extends Extension
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

        if (isset($configs['encoders'])) {
            $this->registerEncoderConfiguration($configs, $container);
        }

        $this->registerProviderConfiguration($configs, $container);

        if ($container->hasParameter("auth.default")) {
            if ($container->has("event_dispatcher")) {
                $dispatcher = $container->get("event_dispatcher");
                $dispatcher->addSubscriber(new AuthorizationListener($container, $container->get($container->getParameter("auth.default")), $configs));
            }
        }
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
            $service = $this->registerDaoProviderConfiguration($providers['dao'], $container);
            $firewall = $configs['firewalls'];
            $this->registerDaoFirewallConfiguration($firewall, $container, $service);
        }
    }

    public function registerDaoProviderConfiguration($configs, ContainerBuilder $container)
    {
        //set an alias to dao.provider
        $container->setAlias('auth', 'dao.provider');
        $container->setParameter('auth.default', 'dao.provider');

        $daoService = $container->get("dao.provider");

        $daoService->setSession($container->get("session"));
        $daoService->SetCookie($container->get("cookie"));

        if (isset($configs["model"])) {
            $daoService->setUserModel($configs["model"]);
        }
        if (isset($configs["model_properties"])) {
            $daoService->setUserProperties($configs["model_properties"]);
        }
        return $daoService;
    }

    public function registerDaoFirewallConfiguration($configs, ContainerBuilder $container, AuthenticationInterface $provider)
    {
        if (isset($configs["secured_area"])) {
            $secured_area = $configs["secured_area"];

            if (isset($secured_area['login'])) {
                $login = $secured_area['login'];
                $provider->setLoginRedirect($login['login_path']);
                $provider->setLoginAction($login['check_path']);
                if (isset($login['error_message'])) {
                    $provider->setLoginError($login['error_message']);
                }
            }

            if (isset($secured_area['logout'])) {
                $logout = $secured_area['logout'];
                $provider->setLogoutRedirect($logout['path']);
            }
        }
    }

}

