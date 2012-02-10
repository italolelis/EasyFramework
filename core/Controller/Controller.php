<?php

App::uses('AnnotationManager', 'Core/Annotations');
App::uses('IComponent', 'Core/Controller');
App::uses('Helper', 'Core/View');
App::uses('AppHelper', 'Helper');

/**
 * Controllers are the core of a web request. They provide actions that
 * will be executed and (generally) render a view that will be sent
  back to the user.

  An action is just a public method on your controller. They're available
  automatically to the user throgh the <Mapper>. Any protected or private
  method will NOT be accessible to requests.

  By default, only your <AppController> will inherit Controller directly.
  All other controllers will inherit AppController, that can contain
  specific rules such as filtering and access control.

  A typical controller will look something like this

  (start code)
  class ArticlesController extends AppController {
  public function index() {
  $this->articles = $this->Articles->all();
  }

  public function view($id = null) {
  $this->article = $this->Articles->firstById($id);
  }
  }
  (end)

  By default, all actions render a view in app/views. A call to the
  index action in the ArticlesController, for example, will render
  the view app/views/articles/index.htm.php.

  All controllers also can load models for you. By default, the
  controller loads the model with the same. Be aware that, if the
  model does not exist, the controller will throw an exception.
  If you don't want the controller to load models, or if you want
  to specific models, use <Controller::$uses>.

  @package easy.controller
 *
 */
abstract class Controller {

    /**
      Defines which models the controller will load. When null, the
      controller will load only the model with the same name of the
      controller. When an empty array, the controller won't load any
      model.

      You can load as many models as you want, but be aware that this
      can decrease your application's performance. So the rule is to
      include here only models you need in all (or almost all)
      actions, and manually load less used models.

      Be aware that, when we start using autoload, this feature will
      be removed, so don't rely on this.

      @see loadModel(), Model::load
     */
    public $uses = null;

    /**
     *  Componentes a serem carregados no controller.
     */
    public $components = array();

    /**
     * Helpers to be used with the view
     * @var array
     */
    public $helpers = array('html', 'form');

    /**
      Contains $_POST and $_FILES data, merged into a single array.
      This is what you should use when getting data from the user.
      A common pattern is checking if there is data in this variable
      like this

     * Exemple:
      <code>
      if(!empty($this->data)) {
      new Articles($this->data)->save();
      }
      </code>
     */
    public $data = array();

    /**
     * This controller's primary model class name, the Inflector::classify()'ed version of
     * the controller's $name property.
     *
     * Example: For a controller named 'Comments', the modelClass would be 'Comment'
     *
     * @var string
     */
    public $modelClass = true;

    /**
     * An instance of a Request object that contains information about the current request.
     * This object contains all the information about a request and several methods for reading
     * additional information about the request.
     *
     * @var Request
     */
    public $request;

    /**
      Defines the name of the controller. Shouldn't be used directly.
      It is used just for loading a default model if none is provided
      and will be removed in the near future.
     */
    protected $name = null;

    /**
      Data to be sent to views. Should not be used directly. Use the
      appropriate methods for this.

      @see
      Controller::__get, Controller::__set, Controller::get,
      Controller::set
     */
    protected $view;

    /**
      Keeps the models attached to the controller. Shouldn't be used
      directly. Use the appropriate methods for this. This will be
      removed when we start using autoload.

      @see
      Controller::__get, Controller::loadModel, Model::load
     */
    protected $models = array();

    /**
      Keeps the components attached to the controller. Shouldn't be used
      directly. Use the appropriate methods for this. This will be
      removed when we start using autoload.

      @see
      Controller::__get, Controller::loadComponent, Model::load
     */
    protected $loadedComponents = array();

    /**
      Keeps the helpers attached to the controller. Shouldn't be used
      directly. Use the appropriate methods for this. This will be
      removed when we start using autoload.

      @see
      Controller::__get, Controller::loadComponent, Model::load
     */
    protected $loadedHelpers = array();

    /**
     * The class name of the parent class you wish to merge with.
     * Typically this is AppController, but you may wish to merge vars with a different
     * parent class.
     *
     * @var string
     */
    protected $_mergeParent = 'AppController';

    public function __construct($request = null) {
        if (is_null($this->name)) {
            $this->name = substr(get_class($this), 0, strlen(get_class($this)) - 10);
        }

        if ($request instanceof Request) {
            $this->setRequest($request);
        }

        $this->view = new View();
        $this->data = $this->request->data; //array_merge_recursive($_POST, $_FILES);
    }

    public function setRequest(Request $request) {
        $this->request = $request;
    }

    public function getView() {
        return $this->view;
    }

    public function setAutoRender($autoRender) {
        $this->view->setAutoRender($autoRender);
    }

    /**
     * Retrieve the controller's name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Provides backwards compatibility access for setting values to the request object.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    /**
     * Provides backwards compatibility access to the request object properties.
     * Also provides the params alias.
     *
     * @param string $name
     * @return void
     */
    public function __get($name) {
        $attrs = array('models', 'loadedComponents', 'loadedHelpers');
        foreach ($attrs as $attr) {
            if (array_key_exists($name, $this->{$attr})) {
                return $this->{$attr}[$name];
            }
        }
    }

    /**
      Sets a value to be sent to the view. It is not commonly used
      anymore, and was abandoned in favor of <Controller::__set>,
      which is much more convenient and readable. Use this only if
      you need extra performance.

      @param $name - name of the variable to be sent to the view. Can
      also be an array where the keys are the name of the
      variables. In this case, $value will be ignored.
      @param $value - value to be sent to the view.
     */
    function set($var, $value = null) {
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                $this->view->set($key, $value);
            }
        } else {
            $this->view->set($var, $value);
        }
    }

    /**
     * Merge components, helpers, and uses vars from Controller::$_mergeParent and PluginAppController.
     *
     * @return void
     */
    protected function _mergeControllerVars() {
        if (is_subclass_of($this, $this->_mergeParent)) {
            $appVars = get_class_vars($this->_mergeParent);
            if ($this->modelClass) {
                if (!in_array($this->name, $this->uses)) {
                    array_unshift($this->uses, $this->name);
                }
            }
            if (($this->uses !== null || $this->uses !== false) && is_array($this->uses) && !empty($appVars['uses'])) {
                $this->uses = array_merge($this->uses, array_diff($appVars['uses'], $this->uses));
            }
            if (($this->components !== null || $this->components !== false) && is_array($this->components) && !empty($appVars['components'])) {
                $this->components = array_merge($this->components, array_diff($appVars['components'], $this->components));
            }
            if (($this->helpers !== null || $this->helpers !== false) && is_array($this->helpers) && !empty($appVars['helpers'])) {
                $this->helpers = array_merge($this->helpers, array_diff($appVars['helpers'], $this->helpers));
            }
        }
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
        //Loads all associate models
        if (!empty($this->uses)) {
            array_map(array($this, 'loadModel'), $this->uses);
        }
        //Loads all associate components
        if (!empty($this->components)) {
            array_map(array($this, 'loadComponent'), $this->components);
        }
        //Loads all associate helpers
        if (!empty($this->helpers)) {
            array_map(array($this, 'loadHelper'), $this->helpers);
        }
        return true;
    }

    /**
     * Instantiates the correct view class, hands it its data, and uses it to render the view output.
     *
     * @return Response A response object containing the rendered view.
     */
    function display() {
        //Raise the beforeRenderEvent for the controllers
        $this->beforeRender();
        $requestedController = Inflector::camelize($this->request->controller);
        $requestedAction = $this->request->action;
        //Display the view
        return $this->view->display("{$requestedController}/{$requestedAction}");
    }

    public function isAjax($action) {
        $annotation = new AnnotationManager("Ajax", $this);
        if ($annotation->hasMethodAnnotation($action)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Call the requested action.
     * @param Request $request The request object.
     * @return type
     * @throws MissingActionException 
     */
    public function callAction() {
        try {
            $method = new ReflectionMethod($this, $this->request->action);
            return $method->invokeArgs($this, $this->request->offsetGet('params'));
        } catch (ReflectionException $e) {
            throw new MissingActionException(array(
                'controller' => $this->request->controller,
                'action' => $this->request->action
                    ), $this->request);
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
        //Notify all components with the initialize event
        $this->componentEvent("initialize");
        //Raise the beforeFilterEvent for the controllers
        $this->beforeFilter();
        //Notify all components with the startup event
        $this->componentEvent("startup");
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
        //Notify all components with the shutdown event
        $this->componentEvent("shutdown");
        //Raise the afterFilterEvent for the controllers
        $this->afterFilter();
    }

    /**
     * Internally redirects one action to another. Does not perform another HTTP request unlike Controller::redirect()
     *
     * Examples:
     *
     * {{{
     * setAction('another_action');
     * setAction('action_with_parameters', $parameter1);
     * }}}
     *
     * @param string $action The new action to be 'redirected' to
     * @param mixed  Any other parameters passed to this method will be passed as
     *    parameters to the new action.
     * @return mixed Returns the return value of the called action
     */
    public function setAction($action) {
        $args = func_get_args();
        unset($args[0]);
        return call_user_func_array(array($this, $action), $args);
    }

    /**
      Loads a model and attaches it to the controller. It is not
      considered a good practice to include all models you will ever
      need in <Controller::$uses>. If you need models that are not
      used throughout your controller, you can load them using this
      method.

      Be aware, though, that is generally better to use <Model::load>
      itself if you don't need to use the instance more than once in
      your action, because it does not have the overhead to attach
      the model to the controller. Also, this method will be removed
      in the next versions in favor of autloading, so don't rely on
      this.

      @param $model - camel-cased name of the model to be loaded.

      @return The model's instance.
     */
    protected function loadModel($model) {
        if (!is_null($model)) {
            if (App::path("App/models", Inflector::camelize($model)))
                return $this->models[$model] = ClassRegistry::load($model);
            else
                throw new MissingModelException($model, array("model" => $model, 'controller' => $this->name));
        }
    }

    /**
     *  Carrega todos os helpers associados ao controller.
     *
     *  @return boolean Verdadeiro se todos os componentes foram carregados
     */
    public function loadHelper($helper) {
        $class = Inflector::camelize($helper . "Helper");

        if (!class_exists($class) && App::path("Helper", $class)) {
            App::uses($class, "Helper");
            $this->loadedHelpers[$helper] = new $class($this->view);
            $this->set($helper, $this->loadedHelpers[$helper]);
            return $this->loadedHelpers[$helper];
        } else {
            throw new MissingHelperException($helper, array('helper' => $helper, 'controller' => $this->name));
        }
    }

    /**
     *  Carrega todos os componentes associados ao controller.
     *
     *  @return boolean Verdadeiro se todos os componentes foram carregados
     */
    public function loadComponent($component) {
        $component = "{$component}Component";
        if (!$this->loadedComponents[$component] = ClassRegistry::load($component, "Component")) {
            throw new MissingComponentException($component, array('component' => $component));
        }
    }

    /**
     *  Executa um evento em todos os componentes do controller.
     *
     *  @param string $event Evento a ser executado
     */
    public function componentEvent($event) {
        foreach ($this->components as $component) {
            $className = "{$component}Component";
            if (method_exists($this->$className, $event)) {
                $this->$className->{$event}($this);
            } else {
                trigger_error("O método {$event} não pode ser chamado na classe {$className}", E_USER_WARNING);
            }
        }
    }

    /**
      Redirects the user to another location.

      @param $url Location to be redirected to.
      @param $status HTTP status code to be sent with the redirect header.
      @param $exit If true, stops the execution of the controller.
     */
    public function redirect($url, $status = null, $exit = true) {
        //Fire the callback beforeRedirect
        $this->beforeRedirect($url, $status, $exit);
        //Don't render anything
        $this->setAutoRender(false);
        $codes = array(
            100 => "Continue",
            101 => "Switching Protocols",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            300 => "Multiple Choices",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            305 => "Use Proxy",
            307 => "Temporary Redirect",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Time-out",
            409 => "Conflict",
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Request Entity Too Large",
            414 => "Request-URI Too Large",
            415 => "Unsupported Media Type",
            416 => "Requested range not satisfiable",
            417 => "Expectation Failed",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Time-out"
        );
        if (!is_null($status) && isset($codes[$status])) {
            header("HTTP/1.1 {$status} {$codes[$status]}");
        }

        header('Location: ' . Mapper::url($url, true));

        if ($exit)
            $this->stop();
    }

    /**
     * Called before the controller action.  You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @return void
     */
    public function beforeFilter() {
        
    }

    /**
     * Called after the controller action is run, but before the view is rendered. You can use this method
     * to perform logic or set view variables that are required on every request.
     *
     * @return void
     */
    public function beforeRender() {
        
    }

    /**
     * The beforeRedirect method is invoked when the controller's redirect method is called but before any
     * further action. If this method returns false the controller will not continue on to redirect the request.
     * The $url, $status and $exit variables have same meaning as for the controller's method. You can also
     * return a string which will be interpreted as the url to redirect to or return associative array with
     * key 'url' and optionally 'status' and 'exit'.
     *
     * @param mixed $url A string or array-based URL pointing to another location within the app,
     *     or an absolute URL
     * @param integer $status Optional HTTP status code (eg: 404)
     * @param boolean $exit If true, exit() will be called after the redirect
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

?>
