<?php

namespace Easy\Mvc;

use Easy\HttpKernel\Bundle\Bundle;
use Easy\Mvc\DependencyInjection\Compiler\RegisterKernelListenersPass;
use Easy\Mvc\EventListener\ParseListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Scope;

class FrameworkBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        $container->addScope(new Scope('request'));

        $container->addCompilerPass(new RegisterKernelListenersPass(), PassConfig::TYPE_AFTER_REMOVING);

        if ($container->has("event_dispatcher")) {
            $dispatcher = $container->get("event_dispatcher");
            $dispatcher->addSubscriber(new ParseListener);
        }
    }

}