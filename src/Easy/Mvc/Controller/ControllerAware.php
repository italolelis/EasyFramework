<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
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