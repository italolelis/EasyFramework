<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Bundles\MenuBundle\EventListener;

use Easy\HttpKernel\Event\GetResponseForControllerResultEvent;
use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class ViewListener implements EventSubscriberInterface
{

    protected $controller;
    protected $container;

    public function onControllerInitialize(InitializeEvent $event)
    {
        $this->controller = $event->getController();
        $this->container = $this->controller->getContainer();
    }

    public function onControllerView(GetResponseForControllerResultEvent $event)
    {
        $this->controller->set("MenuRenderer", $this->container->get("menu.renderer"));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::INITIALIZE => array('onControllerInitialize'),
            KernelEvents::VIEW => array('onControllerView'),
        );
    }

}