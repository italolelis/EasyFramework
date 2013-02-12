<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\HttpKernel;

use Easy\Configure\IConfiguration;
use Easy\Core\Config;
use Easy\Event\EventManager;
use Easy\HttpKernel\Controller\ControllerResolver;
use Easy\HttpKernel\Controller\IControllerResolver;
use Easy\HttpKernel\Event\AfterCallEvent;
use Easy\HttpKernel\Event\BeforeDispatch;
use Easy\HttpKernel\Event\FilterResponseEvent;
use Easy\HttpKernel\Event\GetResponseForExceptionEvent;
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\Controller\ControllerInterface;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Mvc\Controller\Event\StartupEvent;
use Easy\Network\Exception\HttpExceptionInterface;
use Easy\Network\Exception\NotFoundException;
use Easy\Network\Request;
use Easy\Network\Response;
use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Dispatcher é o responsável por receber os parâmetros passados ao EasyFramework
 * através da URL, interpretá-los e direcioná-los para o respectivo controller.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *           
 */
class HttpKernel implements HttpKernelInterface, TerminableInterface
{

    /**
     * @var EventManager
     */
    protected $eventDispatcher;

    /**
     * @var IConfiguration
     */
    protected $kernel;

    /**
     * @var IControllerResolver
     */
    protected $resolver;

    /**
     * Constructor.
     *
     * @param IConfiguration $kernel The IConfiguration class for this app
     * @param IControllerResolver $resolver The controller resolver
     */
    public function __construct(IConfiguration $kernel, IControllerResolver $resolver = null)
    {
        
        if ($resolver === null) {
            $this->resolver = new ControllerResolver();
        }
        $this->kernel = $kernel;
        $this->eventDispatcher = new EventDispatcher();

        $this->subscribeFilterEvents();
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
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        try {
            return $this->handleRaw($request, $type);
        } catch (\Exception $e) {
            if (false === $catch) {
                throw $e;
            }

            return $this->handleException($e, $request, $type);
        }
    }

    /**
     * Filters a response object.
     *
     * @param Response $response A Response instance
     * @param Request  $request  A error message in case the response is not a Response object
     * @param integer  $type     The type of the request (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     *
     * @return Response The filtered Response instance
     *
     * @throws RuntimeException if the passed object is not a Response instance
     */
    private function filterResponse(Response $response, Request $request, $type)
    {
        $event = new FilterResponseEvent($this, $request, $type, $response);
        $this->eventDispatcher->dispatch(KernelEvents::RESPONSE, $event);

        $response = $event->getResponse();
        $response->prepare($request);
        return $response;
    }

    public function handleRaw(Request $request, $type = self::MASTER_REQUEST)
    {
        //filter event
        $this->eventDispatcher->dispatch(KernelEvents::REQUEST, new BeforeDispatch($request));

        //controller
        $controller = $this->resolver->getController($request, $this->kernel);

        // init container
        $this->kernel->initializeAppServices($controller);
        $this->subscribeServicesEvents($this->kernel);
        $this->subscribeControllerEvents($controller);

        if ($controller === false) {
            throw new NotFoundException(__('Unable to find the controller for path "%s". Maybe you forgot to add the matching route in your routing configuration?', $request->getRequestUrl()));
        }

        $response = $this->invoke($controller, $type);

        return $this->filterResponse($response, $request, $type);
    }

    public function subscribeControllerEvents(ControllerInterface $controller)
    {
        $this->eventDispatcher->addListener(KernelEvents::INITIALIZE, array($controller, "beforeFilter"));

        $this->eventDispatcher->addListener(KernelEvents::VIEW, array($controller, "beforeRender"));

        $this->eventDispatcher->addListener(KernelEvents::AFTER_CALL, array($controller, "afterFilter"));
    }

    public function subscribeServicesEvents(KernelInterface $kernel)
    {
        $container = $kernel->getContainer();
        $definitions = $container->getDefinitions();
        foreach ($definitions as $name => $definition) {
            $service = $container->get($name);
            if (method_exists($service, "initialize")) {
                $this->eventDispatcher->addListener(KernelEvents::INITIALIZE, array($service, "initialize"));
            }
            if (method_exists($service, "startup")) {
                $this->eventDispatcher->addListener(KernelEvents::STARTUP, array($service, "startup"));
            }
        }
    }

    protected function subscribeFilterEvents()
    {
        $filters = Config::read('Dispatcher.filters');
        if (empty($filters)) {
            return;
        }
        
        foreach ($filters as $filter) {
            if (is_string($filter)) {
                $class = new $filter();
            }
            if (method_exists($class, "beforeDispatch")) {
                $this->eventDispatcher->addListener(KernelEvents::REQUEST, array($class, "beforeDispatch"));
            }
            if (method_exists($class, "beforeCall")) {
                $this->eventDispatcher->addListener(KernelEvents::INITIALIZE, array($class, "beforeCall"));
            }
            if (method_exists($class, "afterCall")) {
                $this->eventDispatcher->addListener(KernelEvents::AFTER_CALL, array($class, "afterCall"));
            }
            if (method_exists($class, "afterDispatch")) {
                $this->eventDispatcher->addListener(KernelEvents::RESPONSE, array($class, "afterDispatch"));
            }
        }
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
    protected function invoke(Controller $controller, $type)
    {
        //Event
        $this->eventDispatcher->dispatch(KernelEvents::INITIALIZE, new InitializeEvent($controller));
        $this->eventDispatcher->dispatch(KernelEvents::STARTUP, new StartupEvent($controller));
        try {
            $request = $controller->getRequest();
            $method = new ReflectionMethod($controller, $request->action);
            $result = $method->invokeArgs($controller, $request->pass);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException(__('Action %s::%s() could not be found.', $request->controller, $request->action));
        }

        //Event
        $event = new AfterCallEvent($controller, $result);
        $this->eventDispatcher->dispatch(KernelEvents::AFTER_CALL, $event);
        $result = $event->getResult();

        if ($result instanceof Response) {
            return $result;
        }

        // Render the view
        $this->eventDispatcher->dispatch(KernelEvents::VIEW, new Event\GetResponseForControllerResultEvent($this, $request, $type, $result));

        if ($controller->getAutoRender()) {
            $response = $controller->display($controller->getRequest()->action);
        } else {
            $response = new Response();
            $response->setContent($result);
        }

        return $response;
    }

    /**
     * Handles and exception by trying to convert it to a Response.
     *
     * @param \Exception $e       An \Exception instance
     * @param Request    $request A Request instance
     * @param integer    $type    The type of the request
     *
     * @return Response A Response instance
     */
    private function handleException(\Exception $e, $request, $type)
    {
        $event = new GetResponseForExceptionEvent($this, $request, $type, $e);
        $this->eventDispatcher->dispatch(KernelEvents::EXCEPTION, $event);

        // a listener might have replaced the exception
        $e = $event->getException();

        if (!$event->hasResponse()) {
            throw $e;
        }

        $response = $event->getResponse();

        if (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
            // ensure that we actually have an error response
            if ($e instanceof HttpExceptionInterface) {
                // keep the HTTP status code and headers
                $response->setStatusCode($e->getStatusCode());
                $response->headers->add($e->getHeaders());
            } else {
                $response->setStatusCode(500);
            }
        }

        try {
            return $this->filterResponse($response, $request, $type);
        } catch (\Exception $e) {
            return $response;
        }
    }

    public function terminate(Request $request, Response $response)
    {
        $this->eventDispatcher->dispatch(KernelEvents::TERMINATE, new Event\PostResponseEvent($this, $request, $response));
    }

}
