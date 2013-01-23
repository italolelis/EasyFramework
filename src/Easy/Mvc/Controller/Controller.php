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

use Easy\Configure\BaseConfiguration;
use Easy\Configure\IConfiguration;
use Easy\Core\Object;
use Easy\Mvc\Controller\Component\Acl;
use Easy\Mvc\Controller\Component\RequestHandler;
use Easy\Mvc\Controller\Component\Session;
use Easy\Mvc\Controller\Component\Url;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Mvc\Controller\Event\ShutdownEvent;
use Easy\Mvc\Controller\Event\StartupEvent;
use Easy\Mvc\Controller\Metadata\ControllerMetadata;
use Easy\Mvc\Model\IModel;
use Easy\Mvc\Model\ORM\EntityManager;
use Easy\Mvc\ObjectResolver;
use Easy\Mvc\View\View;
use Easy\Network\Exception\NotFoundException;
use Easy\Network\Request;
use Easy\Network\Response;
use Easy\Security\IAuthentication;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
 * @property      Url $Url
 */
abstract class Controller extends Object
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
     * @var Response $response
     */
    protected $response;

    /**
     * @var boolean $autoRender
     */
    protected $autoRender = true;

    /**
     * @var string $name
     */
    protected $name = null;

    /**
     * @var View $view
     */
    protected $view;

    /**
     * @var array $viewVars
     */
    public $viewVars = array();

    /**
     * @var ContainerBuilder $container
     */
    protected $container = null;

    /**
     * @var EventDispatcher $eventDispatcher
     */
    protected $eventDispatcher = null;

    /**
     * @var string $layout
     */
    protected $layout = 'Layout';

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager = null;

    /**
     * @var BaseConfiguration $projectConfiguration
     */
    protected $projectConfiguration;

    /**
     * @var ControllerMetadata $metadata
     */
    protected $metadata;

    /**
     * Initializes a new instance of the Controller class.
     * @param Request $request
     * @param Response $response
     * @param IConfiguration $configs
     */
    public function __construct(Request $request, Response $response, $configs)
    {
        $nameParser = new ControllerNameParser();
        $this->name = $nameParser->parse($this);

        $this->metadata = new ControllerMetadata($this);
        $this->container = new ContainerBuilder();

        $this->eventDispatcher = new EventDispatcher();
        $this->implementedEvents();

        $this->request = $request;
        $this->response = $response;
        $this->projectConfiguration = $configs;

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
     * Gets the project configurations
     * @return IConfiguration
     */
    public function getProjectConfiguration()
    {
        return $this->projectConfiguration;
    }

    /**
     * Gets the project configurations
     * @param IConfiguration $projectConfiguration
     */
    public function setProjectConfiguration($projectConfiguration)
    {
        $this->projectConfiguration = $projectConfiguration;
    }

    /**
     * Gets the EntityManager for this model
     * @return EntityManager 
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Returns the EventManager manager instance that is handling any callbacks
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Sets the request object
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Gets the view object
     * @return View 
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Gets auto render mode
     * @return bool
     */
    public function getAutoRender()
    {
        return $this->autoRender;
    }

    /**
     * Sets auto render mode
     * @param bool $autoRender
     */
    public function setAutoRender($autoRender)
    {
        $this->autoRender = $autoRender;
    }

    /**
     * Gets the IContainer object
     * @return ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets the IContainer object
     * @param ContainerBuilder $container
     */
    public function setContainer(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Gets the layout name. If the method contains an annotation @Layout the will return it's value, otherwise will return the seted value.
     * 
     * @return string
     */
    public function getLayout()
    {
        $layout = $this->metadata->getLayout($this->request->action);
        if ($layout !== null) {
            if ($layout === false) {
                return $this->layout = null;
            } else {
                return $this->layout = $layout;
            }
        } else {
            return $this->layout;
        }
    }

    /**
     * Sets the layout name
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Gets the Response object
     * @return the $response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Gets the Request object
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Retrieve the controller's name
     * @return string
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
            return $this->{$name} = $this->container->get($name);
        }

        throw new RuntimeException(__("Missing property %s", $name));
    }

    /**
     * Sets a value to be sent to the view. It is not commonly used abandoned in favor of <Controller::__set>, which is much more convenient and readable. Use this only if you need extra performance.
     *
     * @param string $name name of the variable to be sent to the view. Can also be an array where the keys are the name of the variables. In this case, $value will be ignored.
     * @param mixed $value value to be sent to the view.
     */
    public function set($one, $two = null)
    {
        if (is_array($one)) {
            if (is_array($two)) {
                $data = array_combine($one, $two);
            } else {
                $data = $one;
            }
        } else {
            $data = array($one => $two);
        }
        $this->viewVars = $data + $this->viewVars;
    }

    /**
     * Initialize the container with all services
     */
    public function constructClasses()
    {

        $this->createDefaultServices(array(
            "RequestHandler",
            "Session",
            "Serializer"
        ));

        $loader = new YamlFileLoader($this->container, new FileLocator(APP_PATH . "Config"));
        $loader->load('services.yml');
        $this->container->compile();

        foreach ($this->container->getServiceIds() as $k) {
            $class = $this->container->get($k);
            if (method_exists($class, "initialize")) {
                $this->eventDispatcher->addListener("initialize", array($class, "initialize"));
            }
            if (method_exists($class, "startup")) {
                $this->eventDispatcher->addListener("startup", array($class, "startup"));
            }
            if (method_exists($class, "shutdown")) {
                $this->eventDispatcher->addListener("shutdown", array($class, "shutdown"));
            }
        }

        if ($this->container->has("Orm")) {
            $this->entityManager = $this->container->get("Orm");
        }
    }

    /**
     * Create the default services to use with container
     * @param array $services The services names
     */
    private function createDefaultServices($services)
    {
        $this->container->register("controller", $this)
                ->addArgument($this->request)
                ->addArgument($this->response)
                ->addArgument($this->projectConfiguration);

        $this->container->register("Url", "Easy\Mvc\Routing\Generator\UrlGenerator")
                ->addArgument($this->request)
                ->addArgument($this->getName());

        foreach ($services as $service) {
            $this->container->register($service, "Easy\Mvc\Controller\Component\\" . $service)
                    ->addMethodCall("setController", array(new Reference("controller")));
        }
    }

    /**
     * Instantiates the correct view class, hands it its data, and uses it to render the view output.
     *
     * @param string $view The view name
     * @param string $controller The controller name
     * @param string $layout The layout to render
     * @param boolean $output If the result should be outputed
     * @return Response
     */
    public function display($view, $controller = true, $layout = null, $output = true)
    {
        if ($controller === true) {
            $controller = $this->name;
        }
        $this->eventDispatcher->dispatch("beforeRender", new ShutdownEvent($this));
        $this->view = new View($this, $this->container->get("Templating"));
        //Pass the view vars to view class
        foreach ($this->viewVars as $key => $value) {
            $this->view->set($key, $value);
        }
        if (!empty($layout)) {
            $this->layout = $layout;
        }

        $content = $this->view->display("{$controller}/{$view}", $this->getLayout(), null, $output);

        //We set the autorender to false, this prevent the action to call this method 2 times
        $this->setAutoRender(false);

        if ($output === true) {
            // Display the view
            $this->response->setContent($content);
            return $this->response;
        } else {
            return $content;
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
     * Perform the various shutdown processes for this controller.
     * Fire the Components and Controller callbacks in the correct order.
     * @return void
     */
    public function shutdownProcess()
    {
        $this->eventDispatcher->dispatch("shutdown", new ShutdownEvent($this));
    }

    /**
     * Internally redirects one action to another.
     * Does not perform another HTTP request unlike Controller::redirect()
     * 
     * @param string $action string The new action to be 'redirected' to
     * @param mixed Any other parameters passed to this method will be passed as parameters to the new action.
     * @return mixed Returns the return value of the called action
     */
    public function forward($action)
    {
        $args = func_get_args();
        unset($args [0]);

        $obj = $this;
        return $obj->{$action}($args);
    }

    /**
     * Redirects the user to another location.
     *
     * @param string $url Location to be redirected to.
     * @param int $status HTTP status code to be sent with the redirect header.
     * @param bool $exit If true, stops the execution of the controller.
     */
    public function redirect($url, $status = null, $exit = true)
    {
        // Don't render anything
        $this->autoRender = false;
        if (!empty($status) && is_string($status)) {
            $codes = array_flip($this->response->httpCodes());
            if (isset($codes [$status])) {
                $status = $codes [$status];
            }
        }

        if ($url !== null) {
            $this->response->header('Location', $url);
        }

        if (!empty($status) && ($status >= 300 && $status < 400)) {
            $this->response->statusCode($status);
        }

        if ($exit) {
            $this->response->send();
            exit(0);
        }
    }

    /**
     * Redirect to a specific action
     * 
     * @param string $actionName The action's name
     * @param string $controllerName The controller's name
     * @param string $params Parameters to send to action
     * @return void
     * @throws LogicException If Url component doesn't exists.
     */
    public function redirectToAction($actionName, $controllerName = true, $params = null)
    {
        if ($controllerName === true) {
            $controllerName = strtolower($this->getName());
        }

        if ($this->container->contains("Url")) {
            $this->redirect($this->Url->action($actionName, $controllerName, $params));
        } else {
            throw new LogicException(__("The Url component isn't intalled. Please check your services config file."));
        }
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
     * Called before the controller action.
     * You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @return void
     */
    public function beforeFilter()
    {
        
    }

    /**
     * Called after the controller action is run, but before the view is
     * rendered.
     * You can use this method
     * to perform logic or set view variables that are required on every
     * request.
     *
     * @return void
     */
    public function beforeRender()
    {
        
    }

    /**
     * Called after the controller action is run and rendered.
     *
     * @return void
     */
    public function afterFilter()
    {
        
    }

}