<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 0.2
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('ClassRegistry', 'Utility');
App::uses('AnnotationManager', 'Annotations');
App::uses('ComponentCollection', 'Controller');
App::uses('View', 'View');
App::uses('Event', 'Event');
App::uses('EventListener', 'Event');
App::uses('EventManager', 'Event');

/**
 * Controllers are the core of a web request.
 *
 * They provide actions that
 * will be executed and (generally) render a view that will be sent
 * back to the user.
 *
 * An action is just a public method on your controller. They're available
 * automatically to the user throgh the <Mapper>. Any protected or private
 * method will NOT be accessible to requests.
 *
 * By default, only your <AppController> will inherit Controller directly.
 * All other controllers will inherit AppController, that can contain
 * specific rules such as filtering and access control.
 *
 * A typical controller will look something like this
 *
 * (start code)
 * class ArticlesController extends AppController {
 * public function index() {
 * $this->articles = $this->Articles->all();
 * }
 *
 * public function view($id = null) {
 * $this->article = $this->Articles->firstById($id);
 * }
 * }
 * (end)
 *
 * By default, all actions render a view in app/views. A call to the
 * index action in the ArticlesController, for example, will render
 * the view app/views/articles/index.htm.php.
 *
 * All controllers also can load models for you. By default, the
 * controller loads the model with the same. Be aware that, if the
 * model does not exist, the controller will throw an exception.
 * If you don't want the controller to load models, or if you want
 * to specific models, use <Controller::$uses>.
 *
 * @package Easy.Controller
 *         
 */
abstract class Controller extends Object implements EventListener {

    /**
     * An array containing the class names of models this controller uses.
     *
     * Example: `public $uses = array('Product', 'Post', 'Comment');`
     *
     * Can be set to several values to express different options:
     *
     * - `true` Use the default inflected model name.
     * - `array()` Use only models defined in the parent class.
     * - `false` Use no models at all, do not merge with parent class either.
     * - `array('Post', 'Comment')` Use only the Post and Comment models. Models
     *   Will also be merged with the parent class.
     *
     * The default value is `true`.
     *
     * @var mixed A single name as a string or a list of names as an array.
     */
    public $uses = true;

    /**
     * Componentes a serem carregados no controller.
     */
    public $components = array('Session');

    /**
     * Helpers to be used with the view
     *
     * @var array
     */
    public $helpers = array('html', 'Form', 'Url');

    /**
     * Contains $_POST and $_FILES data, merged into a single array.
     * This is what you should use when getting data from the user.
     * A common pattern is checking if there is data in this variable
     * like this
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
     * This controller's primary model class name, the Inflector::classify()'ed
     * version of
     * the controller's $name property.
     *
     * Example: For a controller named 'Comments', the modelClass would be
     * 'Comment'
     *
     * @var string
     */
    public $modelClass = null;

    /**
     * An instance of a Request object that contains information about the
     * current request.
     * This object contains all the information about a request and several
     * methods for reading
     * additional information about the request.
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
     * Set to true to automatically render the view
     * after action logic.
     *
     * @var boolean
     */
    protected $autoRender = true;

    /**
     * Defines the name of the controller.
     * Shouldn't be used directly.
     * It is used just for loading a default model if none is provided
     * and will be removed in the near future.
     */
    protected $name = null;

    /**
     * Data to be sent to views.
     * Should not be used directly. Use the
     * appropriate methods for this.
     *
     * @see Controller::__get, Controller::__set, Controller::get,
     *      Controller::set
     */
    protected $view;

    /**
     * Contains variables to be handed to the view.
     *
     * @var array
     */
    public $viewVars = array();

    /**
     * Keeps the models attached to the controller.
     * Shouldn't be used
     * directly. Use the appropriate methods for this. This will be
     * removed when we start using autoload.
     *
     * @see Controller::__get, Controller::loadModel, Model::load
     */
    protected $models = array();

    /**
     * Keeps the components attached to the controller.
     * Shouldn't be used
     * directly. Use the appropriate methods for this. This will be
     * removed when we start using autoload.
     *
     * @see Controller::__get, Controller::loadComponent, Model::load
     */
    protected $Components = array();

    /**
     * Keeps the helpers attached to the controller.
     * Shouldn't be used
     * directly. Use the appropriate methods for this. This will be
     * removed when we start using autoload.
     *
     * @see Controller::__get, Controller::loadComponent, Model::load
     */
    protected $loadedHelpers = array();

    /**
     * The class name of the parent class you wish to merge with.
     * Typically this is AppController, but you may wish to merge vars with a
     * different
     * parent class.
     *
     * @var string
     */
    protected $_mergeParent = 'AppController';

    /**
     * Instance of the EventManager this controller is using
     * to dispatch inner events.
     *
     * @var EventManager
     */
    protected $_eventManager = null;

    public function __construct($request = null, $response = null) {
        if (is_null($this->name)) {
            $this->name = substr(get_class($this), 0, strlen(get_class($this)) - 10);
        }

        if ($request instanceof Request) {
            $this->request = $request;
        }

        if ($response instanceof Response) {
            $this->response = $response;
        }

        $this->modelClass = Inflector::singularize($this->name);
        $this->Components = new ComponentCollection ();

        $this->data = $this->request->data;
    }

    /**
     * Returns a list of all events that will fire in the controller during it's lifecycle.
     * You can override this function to add you own listener callbacks
     *
     * @return array
     */
    public function implementedEvents() {
        return array(
            'Controller.initialize' => 'beforeFilter',
            'Controller.beforeRender' => 'beforeRender',
            'Controller.beforeRedirect' => array('callable' => 'beforeRedirect', 'passParams' => true),
            'Controller.shutdown' => 'afterFilter'
        );
    }

    /**
     * Returns the EventManager manager instance that is handling any callbacks.
     * You can use this instance to register any new listeners or callbacks to the
     * controller events, or create your own events and trigger them at will.
     *
     * @return EventManager
     */
    public function getEventManager() {
        if (empty($this->_eventManager)) {
            $this->_eventManager = new EventManager();
            $this->_eventManager->attach($this->Components);
            $this->_eventManager->attach($this);
        }
        return $this->_eventManager;
    }

    public function setRequest(Request $request) {
        $this->request = $request;
    }

    public function getView() {
        return $this->view;
    }

    public function getAutoRender() {
        return $this->autoRender;
    }

    public function setAutoRender($autoRender) {
        $this->autoRender = $autoRender;
    }

    /**
     * Gets the Response object
     *
     * @return the $response
     */
    public function getResponse() {
        return $this->response;
    }

    public function getRequest() {
        return $this->request;
    }

    /**
     * Retrieve the controller's name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function getComponentCollection() {
        return $this->Components;
    }

    /**
     * Provides backwards compatibility access for setting values to the request
     * object.
     *
     * @param $name string
     * @param $value mixed
     * @return void
     */
    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    /**
     * Provides backwards compatibility access to the request object properties.
     * Also provides the params alias.
     *
     * @param $name string
     * @return void
     */
    public function __get($name) {
        $attrs = array('models', 'Components', 'loadedHelpers');

        foreach ($attrs as $attr) {
            switch ($attr) {
                case 'Components' :
                    return $this->{$attr}->get($name);
                    break;
                default :
                    if (array_key_exists($name, $this->{$attr})) {
                        return $this->{$attr} [$name];
                    }
                    break;
            }
        }
    }

    /**
     * Sets a value to be sent to the view.
     * It is not commonly used
     * anymore, and was abandoned in favor of <Controller::__set>,
     * which is much more convenient and readable. Use this only if
     * you need extra performance.
     *
     * @param $name - name of the variable to be sent to the view. Can
     *        also be an array where the keys are the name of the
     *        variables. In this case, $value will be ignored.
     * @param $value - value to be sent to the view.
     */
    function set($one, $two = null) {
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
     * Merge components, helpers, and uses vars from Controller::$_mergeParent
     * and PluginAppController.
     *
     * @return void
     */
    protected function _mergeControllerVars() {
        $defaultVars = get_class_vars('Controller');
        $mergeParent = is_subclass_of($this, $this->_mergeParent);
        $appVars = array();

        if ($mergeParent) {
            $appVars = get_class_vars($this->_mergeParent);
            $uses = $appVars['uses'];

            $appVars['components'] = Set::merge($appVars ['components'], $defaultVars['components']);
            if (($this->components !== null || $this->components !== false) && is_array($this->components) && !empty($appVars ['components'])) {
                $this->components = Set::merge($this->components, array_diff($appVars ['components'], $this->components));
            }
            //Here we merge the default helper values with AppController
            $appVars['helpers'] = Set::merge($appVars ['helpers'], $defaultVars['helpers']);
            if (($this->helpers !== null || $this->helpers !== false) && is_array($this->helpers) && !empty($appVars ['helpers'])) {
                $this->helpers = Set::merge($this->helpers, array_diff($appVars ['helpers'], $this->helpers));
            }
        }

        if ($this->uses === null) {
            $this->uses = false;
        }
        if ($this->uses === true) {
            $this->uses = array($this->modelClass);
        }
        if (isset($appVars['uses']) && $appVars['uses'] === $this->uses) {
            array_unshift($this->uses, $this->modelClass);
        }

        if ($this->uses !== false) {
            $this->_mergeUses($appVars);
        } else {
            $this->uses = array();
            $this->modelClass = '';
        }
    }

    /**
     * Helper method for merging the $uses property together.
     *
     * Merges the elements not already in $this->uses into
     * $this->uses.
     *
     * @param mixed $merge The data to merge in.
     * @return void
     */
    protected function _mergeUses($merge) {
        if (!isset($merge['uses'])) {
            return;
        }
        if ($merge['uses'] === true) {
            return;
        }
        $this->uses = array_merge(
                $this->uses, array_diff($merge['uses'], $this->uses)
        );
    }

    /**
     * Loads Model classes based on the uses property
     * see Controller::loadModel(); for more info.
     * Loads Components and prepares them for initialization.
     *
     * @return mixed true if models found and instance created.
     * @see Controller::loadModel()
     * @throws MissingModelException
     */
    public function constructClasses() {
        $this->_mergeControllerVars();
        // Loads all associate components
        $this->Components->init($this);

        // Loads all associate models
        if (!empty($this->uses)) {
            array_map(array($this, 'loadModel'), $this->uses);
        }

        return true;
    }

    /**
     * Instantiates the correct view class, hands it its data, and uses it to
     * render the view output.
     *
     * @return Response A response object containing the rendered view.
     */
    function display() {
        // Raise the beforeRenderEvent for the controllers
        $this->beforeRender();
        $requestedController = Inflector::camelize($this->request->controller);
        $requestedAction = $this->request->action;

        $this->view = new View($this);
        // Loads all associate helpers
        $this->view->loadHelpers($this);

        foreach ($this->viewVars as $key => $value) {
            $this->view->set($key, $value);
        }

        // Display the view
        $this->response->body($this->view->display("{$requestedController}/{$requestedAction}"));
        return $this->response;
    }

    public function isAjax($action) {
        $annotation = new AnnotationManager("Ajax", $this);
        if ($annotation->hasAnnotation($action)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Verify if the requested method(POST, GET, PUT, DELETE) is permited to the action 
     * @param Action $action
     * @return boolean True if the requested method matches the permited methods
     */
    public function restApi($action) {
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
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Call the requested action.
     *
     * @param $request Request The request object.
     * @return type
     * @throws MissingActionException
     */
    public function callAction() {
        try {
            $method = new ReflectionMethod($this, $this->request->action);
            return $method->invokeArgs($this, $this->request->params);
        } catch (ReflectionException $e) {
            throw new MissingActionException(null, array(
                'controller' => $this->request->controller,
                'action' => $this->request->action,
                'title' => 'Action Not found'
            ));
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
    public function startupProcess() {
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
    public function shutdownProcess() {
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
    public function setAction($action) {
        $args = func_get_args();
        unset($args [0]);
        return call_user_func_array(array($this, $action), $args);
    }

    /**
     * Loads a model and attaches it to the controller.
     * It is not
     * considered a good practice to include all models you will ever
     * need in <Controller::$uses>. If you need models that are not
     * used throughout your controller, you can load them using this
     * method.
     *
     * Be aware, though, that is generally better to use <Model::load>
     * itself if you don't need to use the instance more than once in
     * your action, because it does not have the overhead to attach
     * the model to the controller. Also, this method will be removed
     * in the next versions in favor of autloading, so don't rely on
     * this.
     *
     * @param $model - camel-cased name of the model to be loaded.
     *       
     * @return The model's instance.
     */
    protected function loadModel($model) {
        if (!is_null($model)) {
            $model = Inflector::singularize($model);
            if (App::path("Model", $model)) {
                $class = $this->models [$model] = ClassRegistry::load($model);
                $class->data = $this->data;
                return $class;
            }else
                throw new MissingModelException(null, array(
                    "model" => $model,
                    'controller' => $this->name,
                    'title' => 'Model Class not found'
                ));
        }
    }

    /**
     * Redirects the user to another location.
     *
     * @param $url Location to be redirected to.
     * @param $status HTTP status code to be sent with the redirect header.
     * @param $exit If true, stops the execution of the controller.
     */
    public function redirect($url, $status = null, $exit = true) {
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
     * Returns the referring URL for this request.
     *
     * @param string $default Default URL to use if HTTP_REFERER cannot be read from headers
     * @param boolean $local If true, restrict referring URLs to local server
     * @return string Referring URL
     */
    public function referer($default = null, $local = false) {
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
     * Called before the controller action.
     * You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @return void
     */
    public function beforeFilter() {
        
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
    public function beforeRender() {
        
    }

    /**
     * The beforeRedirect method is invoked when the controller's redirect
     * method is called but before any
     * further action.
     * If this method returns false the controller will not continue on to
     * redirect the request.
     * The $url, $status and $exit variables have same meaning as for the
     * controller's method. You can also
     * return a string which will be interpreted as the url to redirect to or
     * return associative array with
     * key 'url' and optionally 'status' and 'exit'.
     *
     * @param $url mixed A string or array-based URL pointing to another location
     *        within the app,
     *        or an absolute URL
     * @param $status integer Optional HTTP status code (eg: 404)
     * @param $exit boolean If true, exit() will be called after the redirect
     * @return boolean
     */
    public function beforeRedirect($url, $status = null, $exit = true) {
        return true;
    }

    /**
     * Called after the controller action is run and rendered.
     *
     * @return void
     */
    public function afterFilter() {
        
    }

}
