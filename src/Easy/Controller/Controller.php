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

namespace Easy\Controller;

use Easy\Annotations\AnnotationManager;
use Easy\Controller\ComponentCollection;
use Easy\Controller\Exception\MissingActionException;
use Easy\Core\App;
use Easy\Core\Config;
use Easy\Core\Object;
use Easy\Error;
use Easy\Event\Event;
use Easy\Event\EventListener;
use Easy\Event\EventManager;
use Easy\Model\Model;
use Easy\Model\ORM\EntityManager;
use Easy\Network\Request;
use Easy\Network\Response;
use Easy\Routing\Mapper;
use Easy\Utility\Hash;
use Easy\View\View;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

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
 * @property      \Easy\Controller\Component\Acl $Acl
 * @property      \Easy\Controller\Component\Auth $Auth
 * @property      \Easy\Controller\Component\Cookie $Cookie
 * @property      \Easy\Controller\Component\Email $Email
 * @property      \Easy\Controller\Component\RequestHandler $RequestHandler
 * @property      \Easy\Controller\Component\Security $Security
 * @property      \Easy\Controller\Component\Session $Session
 */
abstract class Controller extends Object implements EventListener
{

    /**
     * Componentes a serem carregados no controller.
     */
    protected $requiredComponents;

    /**
     * Helpers to be used with the view
     * @var array
     */
    public $helpers = array('Html', 'Form', 'Url');

    /**
     * Contains $_POST and $_FILES data, merged into a single array.
     * This is what you should use when getting data from the user.
     * A common pattern is checking if there is data in this variable like this
     *
     * Exemple:
     * <code>
     * if(!empty($this->data)) {
     * new Articles($this->data)->save();
     * }
     * </code>
     */
    public $data = array();

    /**
     * An instance of a Request object that contains information about the current request.
     * This object contains all the information about a request and several methods for reading additional information about the request.
     *
     * @var Request
     */
    public $request;

    /**
     * An instance of a Response object that contains information about the impending response
     *
     * @var Response
     */
    protected $response;

    /**
     * Set to true to automatically render the view after action logic.
     *
     * @var boolean
     */
    protected $autoRender = true;

    /**
     * Defines the name of the controller. Shouldn't be used directly.
     * It is used just for loading a default model if none is provided and will be removed in the near future.
     */
    protected $name = null;

    /**
     * Data to be sent to views. Should not be used directly. Use the appropriate methods for this.
     * 
     * @var View $view
     */
    protected $view;

    /**
     * Contains variables to be handed to the view.
     *
     * @var array
     */
    public $viewVars = array();

    /**
     * Keeps the components attached to the controller. Shouldn't be used directly. Use the appropriate methods for this. This will be removed when we start using autoload.
     *
     * @see Controller::__get, Controller::loadComponent
     */
    protected $components = array();

    /**
     * The class name of the parent class you wish to merge with.
     * Typically this is AppController, but you may wish to merge vars with a different parent class.
     *
     * @var string
     */
    protected $mergeParent = 'App\Controller\AppController';

    /**
     * Instance of the EventManager this controller is using to dispatch inner events.
     *
     * @var EventManager
     */
    protected $eventManager = null;

    /**
     * The name of the layout file to render the view inside of. The name specified is the filename of the layout in /app/View/Layouts without the .tpl extension.
     *
     * @var string
     */
    protected $layout = 'Layout';

    /**
     * The EntityManager object that handles the ORM interface
     * @var EntityManager 
     */
    protected $entityManager = null;

    public function __construct(Request $request = null, Response $response = null)
    {
        if ($this->name === null) {
            list(, $this->name) = namespaceSplit(get_class($this));
            $this->name = substr($this->name, 0, -10);
        }

        if ($request instanceof Request) {
            $this->request = $request;
        }

        if ($response instanceof Response) {
            $this->response = $response;
        }

        $this->components = new ComponentCollection();
        $this->requiredComponents = new \Easy\Collections\Dictionary(Config::read('Components'));
        $this->requiredComponents->add('Session', array());
        $datasourceConfig = Config::read("datasource");
        if ($datasourceConfig) {
            $this->entityManager = new EntityManager($datasourceConfig, App::getEnvironment());
        }
        $this->data = $this->request->data;
    }

    /**
     * Returns a list of all events that will fire in the controller during it's lifecycle.
     * You can override this function to add you own listener callbacks
     *
     * @return array
     */
    public function implementedEvents()
    {
        return array(
            'Controller.initialize' => 'beforeFilter',
            'Controller.beforeRender' => 'beforeRender',
            'Controller.beforeRedirect' => array('callable' => 'beforeRedirect', 'passParams' => true),
            'Controller.shutdown' => 'afterFilter'
        );
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
     * @return EventManager
     */
    public function getEventManager()
    {
        if (empty($this->eventManager)) {
            $this->eventManager = new EventManager();
            $this->eventManager->attach($this);
            $this->eventManager->attach($this->components);
        }
        return $this->eventManager;
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
        $annotation = new AnnotationManager("Layout", $this);
        if ($annotation->hasMethodAnnotation($this->request->action)) {
            //Get the anotation object
            $layout = $annotation->getAnnotationObject($this->request->action)->value;
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
     *
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
        return $this->components;
    }

    /**
     * Provides backwards compatibility to avoid problems with empty and isset to alias properties.
     *
     * @param string $name
     * @return void
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'base':
            case 'here':
            case 'public':
            case 'data':
            case 'action':
            case 'params':
                return true;
        }
        return false;
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
        if ($this->requiredComponents->contains($name)) {
            return $this->{$name} = $value;
        }

        if (in_array($name, $this->helpers)) {
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

            if ($appVars['components'] !== $defaultVars['components']) {
                $appVars['components'] = Hash::merge($appVars ['components'], $defaultVars['components']);
            }

            if (($this->requiredComponents !== null || $this->requiredComponents !== false) && is_array($this->requiredComponents) && !empty($defaultVars ['components'])) {
                $this->requiredComponents = Hash::merge($this->requiredComponents, array_diff($appVars ['components'], $this->requiredComponents));
            }

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
        $this->components->init($this);
        return true;
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
        // Raise the beforeRenderEvent for the controllers
        $this->getEventManager()->dispatch(new Event('Controller.beforeRender', $this));

        $this->view = new View($this);
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
        if ($annotation->hasAnnotation($action)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Verify if the requested method(POST, GET, PUT, DELETE) is permited to the action 
     * @param string $action The action name
     * @return boolean True if the requested method matches the permited methods
     */
    public function restApi($action)
    {
        $annotation = new AnnotationManager("Rest", $this);
        //If the method has the anotation Rest
        if ($annotation->hasMethodAnnotation($action)) {
            //Get the anotation object
            $restAvaliableRequest = $annotation->getAnnotationObject($action);
            //Get the requested method
            $requestedMethod = $this->request->method();
            //If the requested method is in the permited array
            if (in_array($requestedMethod, (Array) $restAvaliableRequest->value)) {
                return true;
            } else {
                throw new Error\UnauthorizedException(__("You can not access this."));
            }
        } else {
            return true;
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
     *
     * - Initializes components, which fires their `initialize` callback
     * - Calls the controller `beforeFilter`.
     * - triggers Component `startup` methods.
     *
     * @return void
     */
    public function startupProcess()
    {
        $this->getEventManager()->dispatch(new Event('Controller.initialize', $this));
        $this->getEventManager()->dispatch(new Event('Controller.startup', $this));
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
        $this->getEventManager()->dispatch(new Event('Controller.shutdown', $this));
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
    public function setAction($action)
    {
        $args = func_get_args();
        unset($args [0]);
        unset($args [1]);

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
        // Fire the callback beforeRedirect
        $this->beforeRedirect($url, $status, $exit);

        if (!empty($status) && is_string($status)) {
            $codes = array_flip($this->response->httpCodes());
            if (isset($codes [$status])) {
                $status = $codes [$status];
            }
        }

        if ($url !== null) {
            $this->response->header('Location', Mapper::url($url, true));
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
     */
    public function redirectToAction($actionName, $controllerName = true, $params = null)
    {
        if ($controllerName === true) {
            $controllerName = strtolower($this->getName());
        }
        $this->redirect(Mapper::url(array(
                    'controller' => $controllerName,
                    'action' => $actionName,
                    'params' => $params
                )));
    }

    /**
     * Returns the referring URL for this request.
     *
     * @param string $default Default URL to use if HTTP_REFERER cannot be read from headers
     * @param boolean $local If true, restrict referring URLs to local server
     * @return string Referring URL
     */
    public function referer($default = null, $local = false)
    {
        if ($this->request) {
            $referer = $this->request->referer($local);
            if ($referer == '/' && $default != null) {
                return Mapper::url($default, true);
            }
            return $referer;
        }
        return '/';
    }

    /**
     * Updates the specified model instance using values from the controller's current value provider.
     * @param Model $model The Model instance to update
     * @param array $data The data that will be updated in Model
     * @return Model
     * @throws Error\InvalidArgumentException If the model is null
     */
    public function updateModel(Model $model, array $data = array())
    {
        if ($model === null) {
            throw Error\InvalidArgumentException(_("The model can't be null"));
        }

        if (empty($data)) {
            $data = $this->data;
        }

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $model->{$key} = $value;
            }
        }

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
     * The beforeRedirect method is invoked when the controller's redirect method is called but before any further action.
     * If this method returns false the controller will not continue on to redirect the request.
     * The $url, $status and $exit variables have same meaning as for the controller's method. You can also return a string which will be interpreted as the url to redirect to or return associative array with key 'url' and optionally 'status' and 'exit'.
     *
     * @param $url mixed A string or array-based URL pointing to another location within the app, or an absolute URL
     * @param $status integer Optional HTTP status code (eg: 404)
     * @param $exit boolean If true, exit() will be called after the redirect
     * @return boolean
     */
    public function beforeRedirect($url, $status = null, $exit = true)
    {
        return true;
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
