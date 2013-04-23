<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel;

use Easy\Configure\IConfiguration;
use Easy\Event\EventManager;
use Easy\HttpKernel\Controller\ControllerResolverInterface;
use Easy\HttpKernel\Event\FilterResponseEvent;
use Easy\HttpKernel\Event\GetResponseEvent;
use Easy\HttpKernel\Event\GetResponseForControllerResultEvent;
use Easy\HttpKernel\Event\GetResponseForExceptionEvent;
use Easy\Mvc\Controller\Controller;
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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    protected $dispatcher;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var ControllerResolverInterface
     */
    protected $resolver;

    /**
     * Constructor.
     *
     * @param IConfiguration $kernel The IConfiguration class for this app
     * @param ControllerResolverInterface $resolver The controller resolver
     */
    public function __construct(EventDispatcherInterface $dispatcher, IConfiguration $kernel, ControllerResolverInterface $resolver = null)
    {
        $this->kernel = $kernel;
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver;
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
        $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

        $response = $event->getResponse();

        return $response;
    }

    public function handleRaw(Request $request, $type = self::MASTER_REQUEST)
    {
        // request
        $event = new GetResponseEvent($this->kernel, $request, $type);
        $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $request, $type);
        }

        // load controller
        $controller = $this->resolver->getController($request);
        
        if ($controller === false) {
            throw new NotFoundException(__('Unable to find the c    ontroller for path "%s". Maybe you forgot to add the matching route in your routing configuration?', $request->getRequestUrl()));
        }

        $event = new InitializeEvent($controller, $request);
        $this->dispatcher->dispatch(KernelEvents::INITIALIZE, $event);
        $request = $event->getRequest();

        // call controller
        $response = $this->invoke($controller, $request, $type);

        return $this->filterResponse($response, $request, $type);
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
    protected function invoke($controller, $request, $type)
    {
        //Event
        $this->dispatcher->dispatch(KernelEvents::STARTUP, new StartupEvent($controller, $request));

        // controller arguments
        $arguments = $this->resolver->getArguments($request, $controller);

        $method = new ReflectionMethod($controller[0], $controller[1]);
        $response = $method->invokeArgs($controller[0], $arguments);

        // view
        if (!$response instanceof Response) {
            //Event
            $event = new GetResponseForControllerResultEvent($this, $request, $type, $response);
            $this->dispatcher->dispatch(KernelEvents::VIEW, $event);

            $response = $event->getResponse();
        }

        return $response;
    }

    public function terminate(Request $request, Response $response)
    {
        $this->dispatcher->dispatch(KernelEvents::TERMINATE, new Event\PostResponseEvent($this, $request, $response));
    }

    /**
     * Handles an exception by trying to convert it to a Response.
     *
     * @param \Exception $e       An \Exception instance
     * @param Request    $request A Request instance
     * @param integer    $type    The type of the request
     *
     * @return Response A Response instance
     *
     * @throws \Exception
     */
    private function handleException(\Exception $e, $request, $type)
    {
        $event = new GetResponseForExceptionEvent($this, $request, $type, $e);
        $this->dispatcher->dispatch(KernelEvents::EXCEPTION, $event);

        // a listener might have replaced the exception
        $e = $event->getException();

        if (!$event->hasResponse()) {
            throw $e;
        }

        $response = $event->getResponse();

        // the developer asked for a specific status code
        if ($response->headers->has('X-Status-Code')) {
            $response->setStatusCode($response->headers->get('X-Status-Code'));

            $response->headers->remove('X-Status-Code');
        } elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
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

    private function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }

        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }

            return sprintf("Array(%s)", implode(', ', $a));
        }

        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }

        if (null === $var) {
            return 'null';
        }

        if (false === $var) {
            return 'false';
        }

        if (true === $var) {
            return 'true';
        }

        return (string) $var;
    }

}
