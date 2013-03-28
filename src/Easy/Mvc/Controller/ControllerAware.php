<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller;

use Easy\Core\Object;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Mvc\Controller\Event\ShutdownEvent;
use Easy\Mvc\Controller\Event\StartupEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * A simple implementation of ControllerAwareInterface.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
abstract class ControllerAware extends Object implements ControllerAwareInterface
{

    /**
     * The controller object
     * @var Controller
     */
    protected $controller;

    /**
     * {@inheritdoc}
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     *  {@inheritdoc}
     */
    public function setController(Controller $controller = null)
    {
        $this->controller = $controller;
    }

    /**
     * Called before the Controller::beforeFilter().
     *
     * @param Event $event
     * @return void
     */
    public function initialize(InitializeEvent $event)
    {
        
    }

    /**
     * Called after the Controller::beforeFilter() and before the controller action
     *
     * @param Event $event
     * @return void
     */
    public function startup(StartupEvent $event)
    {
        
    }

    /**
     * Called after Controller::render() and before the output is printed to the browser.
     *
     * @param Event $event
     * @return void
     */
    public function shutdown(ShutdownEvent $event)
    {
        
    }

}