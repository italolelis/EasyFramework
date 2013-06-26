<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc;

use Easy\HttpKernel\Bundle\Bundle;
use Easy\Mvc\DependencyInjection\Compiler\RegisterKernelListenersPass;
use Easy\Mvc\DependencyInjection\Compiler\RoutingResolverPass;
use Easy\Mvc\DependencyInjection\Compiler\SerializerPass;
use Easy\Mvc\DependencyInjection\Compiler\TranslatorPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;

class FrameworkBundle extends Bundle
{

    public function boot()
    {
        if ($trustedProxies = $this->container->getParameter('kernel.trusted_proxies')) {
            Request::setTrustedProxies($trustedProxies);
        }

        if ($this->container->getParameter('kernel.http_method_override')) {
            Request::enableHttpMethodParameterOverride();
        }
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addScope(new Scope('request'));

        $container->addCompilerPass(new RoutingResolverPass());
        $container->addCompilerPass(new RegisterKernelListenersPass(), PassConfig::TYPE_AFTER_REMOVING);
        $container->addCompilerPass(new TranslatorPass());
        $container->addCompilerPass(new SerializerPass());
    }

}