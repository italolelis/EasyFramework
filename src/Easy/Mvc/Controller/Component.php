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
 * Base class for an individual Component.  Components provide reusable bits of
 * controller logic that can be composed into a controller.  Components also
 * provide request life-cycle callbacks for injecting logic at specific points.
 *
 * ## Life cycle callbacks
 *
 * Components can provide several callbacks that are fired at various stages of the request
 * cycle.  The available callbacks are:
 *
 * - `initialize()` - Fired before the controller's beforeFilter method.
 * - `startup()` - Fired after the controller's beforeFilter method.
 * - `beforeRender()` - Fired before the view + layout are rendered.
 * - `shutdown()` - Fired after the action is complete and the view has been rendered
 *    but before Controller::afterFilter().
 * - `beforeRedirect()` - Fired before a redirect() is done.
 *
 * @package       Easy.Controller
 * @see Controller::$components
 */
class Component extends Object
{

    /**
     * The controller object
     * @var Controller 
     */
    protected $controller;

    public function getController()
    {
        return $this->controller;
    }

    public function setController(Controller $controller)
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