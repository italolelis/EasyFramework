<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @var ControllerInterface
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
    public function setController(ControllerInterface $controller = null)
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