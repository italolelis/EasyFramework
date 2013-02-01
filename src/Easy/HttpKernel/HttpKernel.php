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

namespace Easy\HttpKernel;

use Easy\Configure\IConfiguration;
use Easy\Core\Config;
use Easy\Event\EventManager;
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\Controller\Exception\MissingActionException;
use Easy\Mvc\Routing\Event\AfterCallEvent;
use Easy\Mvc\Routing\Event\BeforeCallEvent;
use Easy\Mvc\Routing\Event\BeforeDispatch;
use Easy\Mvc\Routing\Event\FilterResponseEvent;
use Easy\Mvc\Routing\Exception\MissingDispatcherFilterException;
use Easy\Network\Controller\ControllerResolver;
use Easy\Network\Controller\IControllerResolver;
use Easy\Network\Exception\NotFoundException;
use Easy\Network\Request;
use Easy\Network\Response;
use Easy\Rest\RestManager;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Dispatcher é o responsável por receber os parâmetros passados ao EasyFramework
 * através da URL, interpretá-los e direcioná-los para o respectivo controller.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *           
 */
class HttpKernel implements HttpKernelInterface {

    /**
     * @var EventManager Event manager, used to handle dispatcher filters
     */
    protected $eventDispatcher;

    /**
     * @var IConfiguration The IConfiguration object wich holds the app configuration 
     */
    protected $configuration;

    /**
     * @var IControllerResolver  The IControllerResolver object
     */
    protected $resolver;

    /**
     * Constructor.
     *
     * @param IConfiguration $configuration The IConfiguration class for this app
     * @param string $base The base directory for the application. Writes `App.base` to Configure.
     */
    public function __construct(IConfiguration $configuration, IControllerResolver $resolver = null) {
        if ($resolver === null) {
            $this->resolver = new ControllerResolver();
        }
        $this->configuration = $configuration;
        $this->eventDispatcher = new EventDispatcher();
        $this->attachFilters();
    }

    /**
     * Attaches all event listeners for this dispatcher instance. Loads the
     * dispatcher filters from the configured locations.
     *
     * @return void
     * @throws MissingDispatcherFilterException
     */
    protected function attachFilters() {
        $filters = Config::read('Dispatcher.filters');
        if (empty($filters)) {
            return;
        }

        foreach ($filters as $filter) {
            if (is_string($filter)) {
                $class = new $filter();
            }
            if (method_exists($class, "beforeDispatch")) {
                $this->eventDispatcher->addListener("beforeDispatch", array($class, "beforeDispatch"));
            }
            if (method_exists($class, "beforeCall")) {
                $this->eventDispatcher->addListener("beforeCall", array($class, "beforeCall"));
            }
            if (method_exists($class, "afterCall")) {
                $this->eventDispatcher->addListener("afterCall", array($class, "afterCall"));
            }
            if (method_exists($class, "afterDispatch")) {
                $this->eventDispatcher->addListener(KernelEvents::RESPONSE, array($class, "afterDispatch"));
            }
        }
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param integer $type    The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param Boolean $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
        //filter event
        $this->eventDispatcher->dispatch("beforeDispatch", new BeforeDispatch($request));

        //controller
        $controller = $this->resolver->getController($request, $this->configuration);

        if ($controller === false) {
            throw new NotFoundException(__('Unable to find the controller for path "%s". Maybe you forgot to add the matching route in your routing configuration?', $request->getRequestUrl()));
        }

        $response = $this->invoke($controller);

        $event = new FilterResponseEvent($this, $request, $type, $response);
        $this->eventDispatcher->dispatch(KernelEvents::RESPONSE, $event);

        $event->getResponse();
        $response->prepare($request);
        $response->send();
    }

    /**
     * Initializes the components and models a controller will be using.
     * Triggers the controller action, and invokes the rendering if Controller::$autoRender is true
     * and echo's the output.
     * Otherwise the return value of the controller action are returned.
     *
     * @param Controller resultoller Controller to invoke
     * @return Response
     */
    protected function invoke(Controller $controller) {
        // Init the controller
        $controller->constructClasses();
        // Start the startup process
        $controller->startupProcess();
        //Event
        $this->eventDispatcher->dispatch("beforeCall", new BeforeCallEvent($controller));

        try {
            $request = $controller->getRequest();
            $method = new ReflectionMethod($controller, $request->action);
            $result = $method->invokeArgs($controller, $request->pass);
        } catch (ReflectionException $e) {
            throw new MissingActionException(__('Action %s::%s() could not be found.', $request->controller, $request->action));
        }

        //Event
        $this->eventDispatcher->dispatch("afterCall", new AfterCallEvent($controller, $result));
        //TODO: move the RestManager to filter
        $manager = new RestManager($controller);
        $result = $manager->formatResult($result);

        if ($result instanceof Response) {
            return $result;
        }

        // Render the view
        if ($controller->getAutoRender()) {
            $response = $controller->display($controller->getRequest()->action);
        } else {
            $response = new Response();
            $response->setContent($result);
        }

        return $response;
    }

}
