<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\EventListener;

use Easy\HttpKernel\Event\GetResponseEvent;
use Easy\HttpKernel\HttpKernelInterface;
use Easy\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets the session on the request.
 *
 * This will also start the session if it was already started during a previous
 * request.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class SessionListener implements EventSubscriberInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->container->has('session') || $request->hasSession()) {
            return;
        }

        $request->setSession($this->container->get('session'));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 128),
        );
    }

}
