<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller;

use Easy\HttpKernel\Kernel;
use Easy\HttpKernel\KernelInterface;
use Easy\Mvc\Controller\Component\Acl;
use Easy\Mvc\Controller\Component\RequestHandler;
use Easy\Mvc\ObjectResolver;
use Easy\Network\Exception\NotFoundException;
use Easy\Network\JsonResponse;
use Easy\Network\RedirectResponse;
use Easy\Network\Request;
use Easy\Security\IAuthentication;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controllers are the core of a web request.
 *
 * They provide actions that will be executed and (generally) render a view that will be sent back to the user.
 * 
 * @property      Acl $Acl
 * @property      IAuthentication $Auth
 * @property      RequestHandler $RequestHandler
 */
abstract class Controller extends ContainerAware
{

    /**
     * @var array $data
     */
    public $data = array();

    /**
     * @var Request $request
     */
    public $request;

    /**
     * @var boolean $autoRender
     */
    protected $autoRender = true;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var ContainerInterface 
     */
    protected $container;

    /**
     * Initializes a new instance of the Controller class.
     * @param Request $request
     * @param KernelInterface $kernel
     */
    public function __construct(Request $request, KernelInterface $kernel)
    {
        $this->request = $request;
        $this->kernel = $kernel;
        $this->data = $this->request->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(Kernel $kernel)
    {
        $this->kernel = $kernel;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityManager()
    {
        if (!$this->has('Orm')) {
            throw new LogicException('The OrmBundle is not registered in your application.');
        }

        return $this->get("Orm");
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoRender()
    {
        return $this->autoRender;
    }

    /**
     * {@inheritdoc}
     */
    public function setAutoRender($autoRender)
    {
        $this->autoRender = $autoRender;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->get('controller.nameparser')->getName();
    }

    /**
     * Provides backwards compatibility access for setting values to the request
     * object.
     *
     * @param $name string
     * @param $value mixed
     * @return void
     */
    public function __set($name, $value)
    {
        $services = $this->container->getDefinitions();
        if (isset($services[strtolower($name)])) {
            return $this->{$name} = $value;
        }

        return $this->set($name, $value);
    }

    /**
     * Provides backwards compatibility access to the request object properties.
     * Also provides the params alias.
     *
     * @param $name string
     * @return void
     */
    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }

        if (isset($this->container) && $this->container->has($name)) {
            $class = $this->container->get($name);
            return $this->{$name} = $class;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function display($name, $layout = null, $output = true)
    {
        return $this->get("templating")->display($name, $layout, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function renderJson($data = null, $status = 200, $headers = array())
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value = null)
    {
        $this->get("templating")->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function forward($action)
    {
        $args = func_get_args();
        unset($args [0]);

        $obj = $this;
        return $obj->{$action}($args);
    }

    /**
     * {@inheritdoc}
     */
    public function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function redirectToAction($actionName, $controllerName = true, $params = null)
    {
        if ($controllerName === true) {
            $controllerName = strtolower($this->getName());
        }
        return $this->redirect($this->Url->create($actionName, $controllerName, $params));
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @param string    $message  A message
     * @param \Exception $previous The previous exception
     *
     * @return NotFoundHttpException
     */
    public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundException($message, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function updateModel($model, array $data = array())
    {
        if ($model === null) {
            throw new InvalidArgumentException(__("The model can't be null"));
        }

        if (empty($data)) {
            $data = $this->data;
        }

        $resolver = new ObjectResolver($model);
        $resolver->setValues($data);
        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

}