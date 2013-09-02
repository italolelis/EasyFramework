<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Easy\HttpKernel\Exception\NotFoundHttpException;
use Easy\Mvc\Controller\Component\Acl;
use Easy\Mvc\Controller\Component\RequestHandler;
use Easy\Security\IAuthentication;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Controller is a simple implementation of a Controller.
 *
 * They provide actions that will be executed and (generally) render a view that will be sent back to the user.
 */
abstract class Controller extends ContainerAware
{

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
    public function getLightAccess()
    {
        if (!$this->has('orm')) {
            throw new LogicException('The LightAccesBundle is not registered in your application.');
        }

        return $this->get("orm");
    }

    /**
     * Gets the doctrine service
     * @return Registry
     * @throws LogicException
     */
    public function getDoctrine()
    {
        if (!$this->has('doctrine')) {
            throw new LogicException('The LightAccesBundle is not registered in your application.');
        }

        return $this->get("doctrine");
    }

    /**
     * Gets the Request object
     * @return Request
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
     * Provides backwards compatibility access to the request object properties.
     * Also provides the params alias.
     *
     * @deprecated since 2.1 going to be removed at 2.2
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
     * Renders the view
     * @param string $name The view's name
     * @param string $layout The layout to use
     * @param bool $output Will the view bem outputed?
     */
    public function render($name, $parameters = array())
    {
        return $this->get("templating")->render($name, $parameters);
    }

    /**
     * Return the view response object
     * @param string $name The view's name
     * @param string $layout The layout to use
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        return $this->get('templating')->renderResponse($view, $parameters, $response);
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
        return $this->redirect($this->generateUrl($routeName, $parameters, $absolute));
    }

    public function generateUrl($route, $parameters = array(), $absolute = false)
    {
        return $this->get('router')->generate($route, $parameters, $absolute);
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @param string $message  A message
     * @param \Exception $previous The previous exception
     *
     * @return NotFoundHttpException
     */
    public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
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

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($data as $key => $value) {
            $accessor->setValue($model, $key, $value);
        }

        return $model;
    }

    /**
     * Get a user from the Security Context
     *
     * @return mixed
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    public function getUser()
    {
        if (!$this->container->has('easy_security')) {
            throw new \LogicException('The EasySecurityBundle is not registered in your application.');
        }

        $user = $this->container->get('auth')->getUser();

        if (!is_object($user)) {
            return null;
        }

        return $user;
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