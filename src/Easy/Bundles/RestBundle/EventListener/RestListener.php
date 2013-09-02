<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\RestBundle\EventListener;

use Easy\HttpKernel\Event\FilterResponseEvent;
use Easy\HttpKernel\Event\GetResponseForControllerResultEvent;
use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Controller\Event\StartupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Exception\RuntimeException;

class RestListener implements EventSubscriberInterface
{

    private $manager;
    private $metadata;
    private $controllerAction;

    public function __construct($manager, $metadata)
    {
        $this->manager = $manager;
        $this->metadata = $metadata;
    }

    public function onControllerInitialize(StartupEvent $event)
    {
        $controller = $event->getController();
        $this->controllerAction = $controller[1];

        $this->manager->setRequest($event->getRequest());
        $this->metadata->setClass($controller[0]);

        $methods = $this->metadata->getMethodAnnotation($this->controllerAction);
        if (!$this->manager->isValidMethod($methods)) {
            throw new RuntimeException(__("You can not access this."));
        }
    }

    public function onView(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        $format = $this->metadata->getFormatAnnotation($this->controllerAction);
        if ($format) {
            $event->setControllerResult($this->manager->formatResult($result, $format));
        }
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $responseCode = $this->metadata->getCodeAnnotation($this->controllerAction);
        $this->manager->sendResponseCode($event->getResponse(), $responseCode);

        $format = $this->metadata->getFormatAnnotation($this->controllerAction);
        if ($format) {
            $this->manager->sendContentType($event->getResponse(), $format);
        }
        $event->setResponse($event->getResponse());
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::STARTUP => array('onControllerInitialize', 1),
            KernelEvents::VIEW => array('onView', 1),
            KernelEvents::RESPONSE => array('onResponse')
        );
    }

}