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

use Easy\Annotations\AnnotationManager;
use Easy\Core\Object;
use Easy\Event\EventListener;
use Easy\Mvc\Controller\Component\Acl;
use Easy\Mvc\Controller\Component\RequestHandler;
use Easy\Mvc\Controller\Component\Security;
use Easy\Mvc\Controller\ComponentCollection;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Mvc\Controller\Event\ShutdownEvent;
use Easy\Mvc\Controller\Event\StartupEvent;
use Easy\Mvc\Controller\Exception\MissingActionException;
use Easy\Mvc\Model\IModel;
use Easy\Mvc\Model\ORM\EntityManager;
use Easy\Mvc\ObjectResolver;
use Easy\Mvc\View\View;
use Easy\Network\Exception\NotFoundException;
use Easy\Network\Request;
use Easy\Network\Response;
use Easy\Utility\Hash;
use InvalidArgumentException;
use LogicException;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Controllers are the core of a web request.
 *
 * They provide actions that will be executed and (generally) render a view that will be sent back to the user.
 *
 * An action is just a public method on your controller. They're available automatically to the user throgh the <Mapper>. Any protected or private method will NOT be accessible to requests.
 *
 * By default, only your <AppController> will inherit Controller directly. All other controllers will inherit AppController, that can contain specific rules such as filtering and access control.
 *
 * A typical controller will look something like this
 *
 * <code>
 * class ArticlesController extends AppController {
 * public function index() {
 * $this->articles = $this->Articles->all();
 * }
 *
 * public function view($id = null) {
 * $this->article = $this->Articles->firstById($id);
 * }
 * }
 * </code>
 *
 * By default, all actions render a view in app/views. A call to the index action in the ArticlesController, for example, will render the view app/views/articles/index.htm.php.
 *
 * @property      Acl $Acl
 * @property      \Easy\Mvc\Controller\Component\Auth $Auth
 * @property      \Easy\Mvc\Controller\Component\Cookie $Cookie
 * @property      \Easy\Mvc\Controller\Component\Email $Email
 * @property      RequestHandler $RequestHandler
 * @property      Security $Security
 * @property      \Easy\Mvc\Controller\Component\Session $Session
 */
abstract class Controller extends Object implements EventListener
{

    /**
     * @var array
     */
    protected $requiredComponents;

    /**
     * @var array
     */
    public $helpers = array('Html', 'Form', 'Url');

    /**
     * @var array
     */
    public $data = array();

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var boolean
     */
    protected $autoRender = true;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var View $view
     */
    protected $view;

    /**
     * @var array
     */
    public $viewVars = array();

    /**
     * @var ContainerBuilder
     */
    protected $container = array();

    /**
     * @var string
     */
    protected $mergeParent = 'App\Controller\AppController';

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher = null;

    /**
     * @var string
     */
    protected $layout = 'Layout';

    /**
     * @var EntityManager 
     */
    protected $entityManager = null;

    /**
     * @var \Easy\Configure\BaseConfiguration 
     */
    protected $projectConfiguration;

    public function __construct(Request $request, Response $response, $configs)
    {
        $nameParser = new ControllerNameParser();
        $this->name = $nameParser->parse($this);
        $this->request = $request;
        $this->response = $response;
        $this->projectConfiguration = $configs;
        $this->eventDispatcher = new EventDispatcher();
        $this->implementedEvents();
        $this->data = $this->request->data;
    }

    public function getProjectConfiguration()
    {
        return $this->projectConfiguration;
    }

    public function setProjectConfiguration($projectConfiguration)
    {
        $this->projectConfiguration = $projectConfiguration;
    }

    /**
     * Returns a list of all events that will fire in the controller during it's lifecycle.
     * You can override this function to add you own listener callbacks
     *
     * @return array
     */
    public function implementedEvents()
    {
        if (method_exists($this, "beforeFilter")) {
            $this->eventDispatcher->addListener("initialize", array($this, "beforeFilter"));
        }
        if (method_exists($this, "afterFilter")) {
            $this->eventDispatcher->addListener("startup", array($this, "afterFilter"));
        }
        if (method_exists($this, "beforeRender")) {
            $this->eventDispatcher->addListener("shutdown", array($this, "beforeRender"));
        }
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
     * Returns the EventManager manager instance that is handling any callbacks.
     * You can use this instance to register any new listeners or callbacks to the controller events, or create your own events and trigger them at will.
     *
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
     * Gets the View Object
     * @return View 
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Get Auto render mode
     * @return boolean
     */
    public function getAutoRender()
    {
        return $this->autoRender;
    }

    /**
     * Set Auto render mode
     * @param boolean $autoRender
     */
    public function setAutoRender($autoRender)
    {
        $this->autoRender = $autoRender;
    }

    /**
     * Get the layout name. If the method contains an annotation @Layout the will return it's value, otherwise will return the seted value.
     * 
     * @return string
     */
    public function getLayout()
    {
        $manager = new AnnotationManager("Layout", $this);
        $annotation = $manager->getMethodAnnotation($this->request->action);

        if (!empty($annotation)) {
            $layout = $annotation->value;
            if (empty($layout)) {
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
     * Gets the component collection
     * @return ComponentCollection
     */
    public function getComponentCollection()
    {
        return $this->container;
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
        if (in_array($name, $this->helpers)) {
            return $this->{$name} = $value;
        }

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
     * @param $name - name of the variable to be sent to the view. Can also be an array where the keys are the name of the variables. In this case, $value will be ignored.
     * @param $value - value to be sent to the view.
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
     * Merge components, helpers
     * @return void
     */
    protected function mergeControllerVars()
    {
        $defaultVars = get_class_vars('Easy\Controller\Controller');
        $mergeParent = is_subclass_of($this, $this->mergeParent);
        $appVars = array();

        if ($mergeParent) {
            $appVars = get_class_vars($this->mergeParent);

            //Here we merge the default helper values with AppController
            if ($appVars['helpers'] !== $defaultVars['helpers']) {
                $appVars['helpers'] = Hash::merge($appVars ['helpers'], $defaultVars['helpers']);
            }

            if (($this->helpers !== null || $this->helpers !== false) && (is_array($this->helpers) && !empty($defaultVars ['helpers']))) {
                $this->helpers = Hash::merge($this->helpers, array_diff($appVars ['helpers'], $this->helpers));
            }
        }
    }

    /**
     * Merges this objects $property with the property in $class' definition.
     * This classes value for the property will be merged on top of $class'
     *
     * This provides some of the DRY magic CakePHP provides.  If you want to shut it off, redefine this method as an empty function.
     *
     * @param array $properties The name of the properties to merge.
     * @param string $class The class to merge the property with.
     * @param boolean $normalize Set to true to run the properties through Hash::normalize() before merging.
     * @return void
     */
    protected function mergeVars($properties, $class, $normalize = true)
    {
        $classProperties = get_class_vars($class);
        foreach ($properties as $var) {
            if (
                    isset($classProperties[$var]) &&
                    !empty($classProperties[$var]) &&
                    is_array($this->{$var}) &&
                    $this->{$var} != $classProperties[$var]
            ) {
                if ($normalize) {
                    $classProperties[$var] = Hash::normalize($classProperties[$var]);
                    $this->{$var} = Hash::normalize($this->{$var});
                }
                $this->{$var} = Hash::merge($classProperties[$var], $this->{$var});
            }
        }
    }

    public function constructClasses()
    {
        $this->mergeControllerVars();

        $this->container = new ContainerBuilder();
        $this->container->register("controller", $this)
                ->addArgument($this->request)
                ->addArgument($this->response)
                ->addArgument($this->projectConfiguration);

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
        $this->eventDispatcher->dispatch("shutdown", new ShutdownEvent($this));
        $this->view = new View($this, $this->container->get("Templating"));
        //Pass the view vars to view class
        foreach ($this->viewVars as $key => $value) {
            $this->view->set($key, $value);
        }
        if (!empty($layout)) {
            $this->layout = $layout;
        }

        $response = $this->view->display("{$controller}/{$view}", $this->getLayout(), null, $output);

        //We set the autorender to false, this prevent the action to call this 2 times
        $this->setAutoRender(false);

        if ($output === true) {
            // Display the view
            $this->response->body($response);
            return $this->response;
        } else {
            return $response;
        }
    }

    /**
     * Check if an requested action is annoted with ajax return
     * @param string $action The action name to check
     * @return boolean
     */
    public function isAjax($action)
    {
        $annotation = new AnnotationManager("Ajax", $this);
        if ($annotation->getAnnotation($action)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Call the requested action.
     * 
     * @return mixed
     * @throws MissingActionException
     */
    public function callAction()
    {
        try {
            $method = new ReflectionMethod($this, $this->request->action);
            return $method->invokeArgs($this, $this->request->pass);
        } catch (ReflectionException $e) {
            throw new MissingActionException(__('Action %s::%s() could not be found.', $this->request->controller, $this->request->action));
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
     *
     * - triggers the component `shutdown` callback.
     * - calls the Controller's `afterFilter` method.
     *
     * @return void
     */
    public function shutdownProcess()
    {
        //$this->eventDispatcher->dispatch("shutdown", new ShutdownEvent($this));
    }

    /**
     * Internally redirects one action to another.
     * Does not perform another HTTP request unlike Controller::redirect()
     *
     * Examples:
     *
     * {{{
     * setAction('another_action');
     * setAction('action_with_parameters', $parameter1);
     * }}}
     *
     * @param $action string The new action to be 'redirected' to
     * @param mixed Any other parameters passed to this method will be
     *        passed as
     *        parameters to the new action.
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
     * @param $url Location to be redirected to.
     * @param $status HTTP status code to be sent with the redirect header.
     * @param $exit If true, stops the execution of the controller.
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
            throw new LogicException(__("The Url component isen't intalled. Please check your component config file."));
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
