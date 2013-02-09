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

use Easy\Configure\IConfiguration;
use Easy\Core\Object;
use Easy\HttpKernel\Kernel;
use Easy\Mvc\Controller\Component\Acl;
use Easy\Mvc\Controller\Component\RequestHandler;
use Easy\Mvc\Controller\Component\Session;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Mvc\Controller\Event\ShutdownEvent;
use Easy\Mvc\Controller\Event\StartupEvent;
use Easy\Mvc\Model\IModel;
use Easy\Mvc\Model\ORM\EntityManager;
use Easy\Mvc\ObjectResolver;
use Easy\Mvc\Routing\Generator\UrlGenerator;
use Easy\Network\Exception\NotFoundException;
use Easy\Network\RedirectResponse;
use Easy\Network\Request;
use Easy\Security\IAuthentication;
use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var array $viewVars
     */
    public $viewVars = array();

    /**
     * @var EventDispatcher $eventDispatcher
     */
    protected $eventDispatcher = null;

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager = null;

    /**
     * @var Kernel $projectConfiguration
     */
    protected $kernel;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
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

        $this->eventDispatcher = new EventDispatcher();
        $this->implementedEvents();

        $this->request = $request;
        $this->kernel = $kernel;
        $this->container = $kernel->getContainer();
        $this->Url = new UrlGenerator($this->request, $this->name);

        $this->data = $this->request->data;
    }

    private function implementedEvents()
    {
        if (method_exists($this, "beforeFilter")) {
            $this->eventDispatcher->addListener("initialize", array($this, "beforeFilter"));
        }
        if (method_exists($this, "beforeRender")) {
            $this->eventDispatcher->addListener("beforeRender", array($this, "beforeRender"));
        }
        if (method_exists($this, "afterFilter")) {
            $this->eventDispatcher->addListener("shutdown", array($this, "afterFilter"));
        }
    }

    /**
     * Perform the startup process for this controller.
     * Fire the Components and Controller callbacks in the correct order.
     * @return void
     */
    public function startupProcess()
    {
        $this->eventDispatcher->dispatch("initialize", new InitializeEvent($this));
        $this->eventDispatcher->dispatch("startup", new StartupEvent($this));
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
        return $this->entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
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

        if ($this->container->has($name)) {
            $class = $this->container->get($name);
            if (method_exists($class, "initialize")) {
                $this->eventDispatcher->addListener("initialize", array($class, "initialize"));
            }
            if (method_exists($class, "startup")) {
                $this->eventDispatcher->addListener("startup", array($class, "startup"));
            }
            return $this->{$name} = $class;
        }
    }

    /**
     * Initialize the container with all services
     */
    public function constructClasses()
    {
        $services = array(
            "RequestHandler",
            "Session",
            "Serializer"
        );
        $this->container->set("controller", $this);

        foreach ($services as $service) {
            $this->container->register($service, "Easy\Mvc\Controller\Component\\" . $service)
                    ->addMethodCall("setController", array(new Reference("controller")));
        }

        $loader = new YamlFileLoader($this->container, new FileLocator($this->kernel->getConfigDir()));
        $loader->load('services.yml');

        if ($this->container->has("Orm")) {
            $this->entityManager = $this->container->get("Orm");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function display($action, $controller = true, $layout = null, $output = true)
    {
        if ($controller === true) {
            $controller = $this->name;
        }
        $this->eventDispatcher->dispatch("beforeRender", new ShutdownEvent($this));
        return $this->container->get("templating")->display("{$controller}/{$action}", $layout, $output);
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
     * @param IModel $model The Model instance to update
     * @param array $data The data that will be updated in Model
     * @return IModel
     * @throws InvalidArgumentExceptionl If the model is null
     */
    public function updateModel(IModel $model, array $data = array())
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