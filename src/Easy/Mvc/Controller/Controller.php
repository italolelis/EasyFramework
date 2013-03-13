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

use Easy\Configure\IConfiguration;
use Easy\Core\Object;
use Easy\HttpKernel\Kernel;
use Easy\Mvc\Controller\Component\Acl;
use Easy\Mvc\Controller\Component\RequestHandler;
use Easy\Mvc\Controller\Component\Session;
use Easy\Mvc\Model\IModel;
use Easy\Mvc\ObjectResolver;
use Easy\Mvc\Routing\Generator\UrlGenerator;
use Easy\Network\Exception\NotFoundException;
use Easy\Network\RedirectResponse;
use Easy\Network\Request;
use Easy\Security\IAuthentication;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controllers are the core of a web request.
 *
 * They provide actions that will be executed and (generally) render a view that will be sent back to the user.
 * 
 * @property      Acl $Acl
 * @property      IAuthentication $Auth
 * @property      RequestHandler $RequestHandler
 * @property      Session $Session
 * @property      UrlGenerator $Url
 */
abstract class Controller extends Object implements ControllerInterface
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
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var Kernel $projectConfiguration
     */
    protected $kernel;

    /**
     * @var ContainerInterface 
     */
    protected $container;
    protected $Url;

    /**
     * Initializes a new instance of the Controller class.
     * @param Request $request
     * @param IConfiguration $kernel
     */
    public function __construct(Request $request, Kernel $kernel)
    {
        $nameParser = new ControllerNameParser($this);
        $this->name = $nameParser->getName();
        $this->namespace = $nameParser->getNamespace();
        $this->request = $request;
        $this->kernel = $kernel;
        $this->Url = new UrlGenerator($this->request, $this->name);

        $this->data = $this->request->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
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
        if (!$this->container->has('Orm')) {
            throw new LogicException('The OrmBundle is not registered in your application.');
        }

        return $this->container->get("Orm");
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
        return $this->name;
    }

    public function getUrlGenerator()
    {
        return $this->Url;
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
        return $this->container->get("templating")->display($name, $layout, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value = null)
    {
        $this->container->get("templating")->set($key, $value);
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
     * Updates the specified model instance using values from the controller's current value provider.
     * @param object $model The Model instance to update
     * @param array $data The data that will be updated in Model
     * @return object
     * @throws InvalidArgumentExceptionl If the model is null
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
    public function beforeFilter()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function beforeRender()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function afterFilter()
    {
        
    }

}