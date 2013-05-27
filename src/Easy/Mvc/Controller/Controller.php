<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller;

use Easy\Mvc\Controller\Component\Acl;
use Easy\Mvc\Controller\Component\RequestHandler;
use Easy\Mvc\ObjectResolver;
use Easy\Security\IAuthentication;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller is a simple implementation of a Controller.
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
     * @deprecated since version 2.1 use getRequest()->request->all() instead
     */
    public $data = array();

    /**
     * @var ContainerInterface 
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->data = $container->get('request')->request->all();
        parent::setContainer($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getKernel()
    {
        return $this->get('kernel');
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityManager()
    {
        if (!$this->has('orm')) {
            throw new LogicException('The LightAccesBundle is not registered in your application.');
        }

        return $this->get("orm");
    }

    /**
     * Gets the Request object
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->get('controller.object.nameparser')->getName();
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
        if (isset($this->container) && $this->container->has($name)) {
            $class = $this->container->get($name);
            return $this->{$name} = $class;
        }
    }

    /**
     * Display a view
     * @param string $name The view's name
     * @param string $layout The layout to use
     * @param bool $output Will the view bem outputed?
     * @deprecated since version 2.1 use render instead
     */
    public function display($name, $layout = null, $output = true)
    {
        return $this->render($name, $layout, $output);
    }

    /**
     * Renders the view
     * @param string $name The view's name
     * @param string $layout The layout to use
     * @param bool $output Will the view bem outputed?
     */
    public function render($name, $layout = null, $output = true)
    {
        return $this->get("templating")->render($name, $layout, $output);
    }

    /**
     * Return the view response object
     * @param string $name The view's name
     * @param string $layout The layout to use
     */
    public function renderResponse($name, $layout = null)
    {
        return $this->get('templating')->renderResponse($name, $layout);
    }

    /**
     * Return the a JsonResponse object
     * @param string $name The view's name
     * @param string $layout The layout to use
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
    public function redirectToAction($routeName, $parameters = array(), $absolute = false)
    {
        return $this->generateUrl($routeName, $parameters, $absolute);
    }

    public function generateUrl($route, $parameters = array(), $absolute = false)
    {
        return $this->redirect($this->get('router')->generate($route, $parameters, $absolute));
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
            $data = $this->getRequest()->request->all();
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