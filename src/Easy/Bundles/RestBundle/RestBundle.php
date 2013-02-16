<?php

namespace Easy\Bundles\RestBundle;

use Easy\Bundles\RestBundle\EventListener\RestListener;
use Easy\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RestBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        if ($container->has("event_dispatcher")) {
            $dispatcher = $container->get("event_dispatcher");
            $dispatcher->addSubscriber(new RestListener());
        }
    }

}