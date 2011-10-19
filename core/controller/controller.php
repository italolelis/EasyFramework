<?php

App::import("Core", array("controller/component"));

/**
 *  Controller permite que seja adicionada lógica a uma aplicação, além de prover
 *  funcionalidades básicas, como renderizaçao de views, redirecionamentos, acesso
 *  a modelos de dados, entre outros.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
abstract class Controller extends Hookable {

    /**
     *  Modelos utilizados pelo controller.
     */
    public $uses = null;

    /**
     *  Componentes a serem carregados no controller.
     */
    public $components = array();

    /**
     *  Nome do controller.
     */
    public $name = null;

    /**
     * Dados enviados pelo usuário via POST ou GET
     * @var mixed 
     */
    public $data = array();

    /**
     * Propriedade que receberá um objeto View
     * @var View 
     */
    protected $view;

    /**
     * Define se a view será renderizada automaticamente
     * @var bool 
     */
    public $autoRender = true;
    /*
      Variable: $beforeFilter

      beforeFilters are methods run before a controller action. They
      may stop a action from running, for example when a user does not
      have permission to access certain actions.

      (start code)
      protected $beforeFilter = array('requireLogin');

      protected function requireLogin() {
      if(!$this->loggedIn()) {
      // Controller::redirect stops the action from running
      // and redirects the user to a login page
      $this->redirect('/users/login');
      }
      }
      (end)
     */
    protected $beforeFilter = array();

    /*
      Variable: $beforeRender

      beforeRenders are methods run after a controller action has been
      executed, but before they render any output. You can use it to
      suppress output for some reason.
     */
    protected $beforeRender = array();

    /*
      Variable: $afterFilter

      afterFilters are methods run after the controller executed an
      action and sent output to the browser.
     */
    protected $afterFilter = array();

    /**
     * Layout utilizado para exibir a view
     * @var string 
     */
    public $layout = null;

    function __construct() {
        if (is_null($this->name) && preg_match("/(.*)Controller/", get_class($this), $name)):
            if ($name[1] && $name[1] != "App"):
                $this->name = $name[1];
            elseif (is_null($this->uses)):
                $this->uses = array();
            endif;
        endif;
        if (is_null($this->uses)):
            $this->uses = array($this->name);
        endif;

        $this->view = new View();
        $this->data = array_merge_recursive($_POST, $_FILES);
        $this->loadComponents();
        $this->loadModels();
    }

    /**
     * Mostra uma view
     * @param string $view o nome do template a ser exibido
     * @param string $ext a extenção do arquivo a ser exibido. O padrão é '.tpl'
     * @return View 
     */
    function display($view, $ext = ".tpl") {
        $this->view->layout = $this->layout;
        $this->view->autoRender = $this->autoRender;
        return $this->view->display($view);
    }

    /**
     * Define uma variável que será passada para a view
     * @param string $var o nome da variável que será passada para a view
     * @param mixed $value o valor da varíavel
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

    public static function hasViewForAction($request) {
        return Filesystem::exists('app/views/' . $request['controller'] . '/' . $request['action'] . '.tpl');
    }

    public function callAction($request) {
        if ($this->hasAction($request['action']) || self::hasViewForAction($request)) {
            return $this->dispatch($request);
        } else {
            throw new MissingActionException('action', $request);
        }
    }

    public function hasAction($action) {
        $class = new ReflectionClass(get_class($this));
        if ($class->hasMethod($action)) {
            $method = $class->getMethod($action);
            return $method->class != 'Controller' && $method->isPublic();
        } else {
            return false;
        }
    }

    protected function dispatch($request) {
        //Chamamos o evento initialize dos componentes
        $this->componentEvent("initialize");
        //Chamamos o evento beforeFilter dos controllers
        $this->fireAction('beforeFilter');
        //Chamamos o evento startup dos componentes
        $this->componentEvent("startup");
        if ($this->hasAction($request['action'])) {
            call_user_func_array(array($this, $request['action']), $request['params']);
        }
        //Se o autorender está habilitado
        if ($this->autoRender) {
            //Mostramos a view
            $this->fireAction('beforeRender');
            $this->display("{$request["controller"]}/{$request["action"]}");
        }
        //Chamamos o evento shutdown dos componentes
        $this->componentEvent("shutdown");
        //Chamamos o evento afterFilter dos controllers
        $this->fireAction('afterFilter');
    }

    /*
      Method: load

      Loads a controller. Typically used by the Dispatcher.

      Params:
      $name - class name of the controller to be loaded.
      $instance - true to return an instance of the controller,
      false if you just want the class loaded.

      Returns:
      If $instance == false, returns true if the controller was
      loaded. If $instance == true, returns an instance of the
      controller.

      Throws:
      - MissingControllerException if the controller can't be
      found.

      Todo:
      - Replace by auto-loading.
     */

    public static function load($name, $instance = false) {
        if (!class_exists($name) && App::path("Controller", Inflector::underscore($name))) {
            App::import("Controller", Inflector::underscore($name));
        }

        if (class_exists($name)) {
            if ($instance) {
                return $controller = & ClassRegistry::load($name, "Controller");
            } else {
                return true;
            }
        } else {
            throw new MissingControllerException("controller", array("controller" => $name));
        }
    }

    /**
     *  Carrega todos os models associados ao controller.
     *
     *  @return boolean Verdadeiro caso todos os models foram carregados
     */
    public function loadModels() {
        foreach ($this->uses as $model) {
            if (!$this->{$model} = ClassRegistry::load($model)) {
                throw new MissingModelException("model", array("model" => $model));
                return false;
            }
        }
        return true;
    }

    /**
     *  Carrega todos os componentes associados ao controller.
     *
     *  @return boolean Verdadeiro se todos os componentes foram carregados
     */
    public function loadComponents() {
        foreach ($this->components as $component) {
            $component = "{$component}Component";
            if (!$this->{$component} = ClassRegistry::load($component, "Component")) {
                throw new MissingComponentException("component", array("component" => $component));
                return false;
            }
        }
        return true;
    }

    /**
     *  Executa um evento em todos os componentes do controller.
     *
     *  @param string $event Evento a ser executado
     *  @return void
     */
    public function componentEvent($event) {
        foreach ($this->components as $component):
            $className = "{$component}Component";
            if (method_exists($this->$className, $event)):
                $this->$className->{$event}($this);
            else:
                trigger_error("O método {$event} não pode ser chamado na classe {$className}", E_USER_WARNING);
            endif;
        endforeach;
    }

    /**
     *  Faz um redirecionamento enviando um cabeçalho HTTP com o código de status.
     *
     *  @param string $url URL para redirecionamento
     *  @param integer $status Código do status
     *  @param boolean $exit Verdadeiro para encerrar o script após o redirecionamento
     *  @return void
     */
    public function redirect($url, $status = null, $exit = true) {
        $this->autoRender = false;
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
        if (!is_null($status) && isset($codes[$status])):
            header("HTTP/1.1 {$status} {$codes[$status]}");
        endif;

        header('Location: ' . Mapper::url($url, true));

        if ($exit)
            $this->stop();
    }

}

?>
