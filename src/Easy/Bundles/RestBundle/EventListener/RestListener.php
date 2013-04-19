<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\RestBundle\EventListener;

use Easy\Bundles\RestBundle\RestManager;
use Easy\HttpKernel\Event\FilterResponseEvent;
use Easy\HttpKernel\Event\GetResponseForControllerResultEvent;
use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Controller\Event\StartupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Exception\RuntimeException;

class RestListener implements EventSubscriberInterface
{

    private static $manager;

    public function onControllerInitialize(StartupEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();
        $this->loadManager($controller, $request);

        if (!static::$manager->isValidMethod()) {
            throw new RuntimeException(__("You can not access this."));
        }
    }

    public function onView(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        $event->setControllerResult(static::$manager->formatResult($result));
    }

    public function onAfterRequest(FilterResponseEvent $event)
    {
        static::$manager->sendResponseCode($event->getResponse());
    }

    private function loadManager($controller, $request)
    {
        if (!static::$manager) {
            static::$manager = new RestManager($controller, $request);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::STARTUP => array('onControllerInitialize', 1),
            KernelEvents::VIEW => array('onView', 1),
            KernelEvents::RESPONSE => array('onAfterRequest')
        );
    }

}