<?php

namespace Easy\Bundles\SecurityBundle;

use Easy\Bundles\SecurityBundle\EventListener\AuthenticationListener;
use Easy\Bundles\SecurityBundle\EventListener\AuthorizationListener;
use Easy\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SecurityBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        if ($container->has("event_dispatcher")) {
            $dispatcher = $container->get("event_dispatcher");
            $dispatcher->addSubscriber(new AuthenticationListener());
            $dispatcher->addSubscriber(new AuthorizationListener());
        }
    }

}