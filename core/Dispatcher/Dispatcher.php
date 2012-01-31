<?php

App::uses('Request', "Core/Network");

/**
 *  Dispatcher é o responsável por receber os parâmetros passados ao EasyFramework
 *  através da URL, interpretá-los e direcioná-los para o respectivo controller.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
class Dispatcher {

    /**
     * Dispatches and invokes given Request, handing over control to the involved controller. If the controller is set
     * to autoRender, via Controller::$autoRender, then Dispatcher will render the view.
     *
     * Actions in EasyFramework can be any public method on a controller, that is not declared in Controller.  If you
     * want controller methods to be public and in-accesible by URL, then prefix them with a `_`.
     * For example `public function _loadPosts() { }` would not be accessible via URL.  Private and protected methods
     * are also not accessible via URL.
     *
     * If no controller of given name can be found, invoke() will throw an exception.
     * If the controller is found, and the action is not found an exception will be thrown.
     *
     * @param Request $request Request object to dispatch.
     * @param Response $response Response object to put the results of the dispatch into.
     * @param array $additionalParams Settings array ("bare", "return") which is melded with the GET and POST params
     * @return boolean Success
     * @throws MissingControllerException, MissingActionException, PrivateActionException if any of those error states
     *    are encountered.
     */
    public function dispatch() {
        $request = new Request(Mapper::here());
        $request = $this->parseParams($request);

        $controller = $this->_getController($request);

        if (!($controller instanceof Controller)) {
            throw new MissingControllerException($request->controller, array('controller' => $request->controller));
        }

        return $this->_invoke($controller, $request);
    }

    /**
     * Initializes the components and models a controller will be using.
     * Triggers the controller action, and invokes the rendering if Controller::$autoRender is true and echo's the output.
     * Otherwise the return value of the controller action are returned.
     *
     * @param Controller $controller Controller to invoke
     * @param Request $request The request object to invoke the controller for.
     * @param Response $response The response object to receive the output
     * @return void
     */
    protected function _invoke(Controller $controller, Request $request) {
        //Init the controller
        $controller->constructClasses();
        //Start the startup process
        $controller->startupProcess();
        //Call the action
        $return = $controller->callAction();
        if ($controller->isAjax($request->action)) {
            $controller->setAutoRender(false);
            echo $return;
        } else {
            //Render the view
            $controller->display();
        }
        //Start the shutdown process
        $controller->shutdownProcess();
    }

    /**
     * Applies Routing and additionalParameters to the request to be dispatched.
     * If Routes have not been loaded they will be loaded, and app/Config/routes.php will be run.
     *
     * @param CakeRequest $request CakeRequest object to mine for parameter information.
     * @param array $additionalParams An array of additional parameters to set to the request.
     *   Useful when Object::requestAction() is involved
     * @return CakeRequest The request object with routing params set.
     */
    public function parseParams(Request $request, $additionalParams = array()) {
        $params = Mapper::parse($request->url);
        $request->addParams($params);

        if (!empty($additionalParams)) {
            $request->addParams($additionalParams);
        }
        return $request;
    }

    /**
     * Get controller to use, either plugin controller or application controller
     *
     * @param CakeRequest $request Request object
     * @param CakeResponse $response Response for the controller.
     * @return mixed name of controller if not loaded, or object if loaded
     */
    protected function _getController($request) {
        $ctrlClass = $this->_loadController($request);
        if (!$ctrlClass) {
            return false;
        }
        $reflection = new ReflectionClass($ctrlClass);
        if ($reflection->isAbstract() || $reflection->isInterface()) {
            return false;
        }
        return $reflection->newInstance($request);
    }

    /**
     * Load controller and return controller classname
     *
     * @param CakeRequest $request
     * @return string|bool Name of controller class name
     */
    protected function _loadController($request) {
        //Create the controller class name
        $class = Inflector::camelize($request->controller . 'Controller');
        if (!class_exists($class) && App::path("App/controllers", $class)) {
            App::uses($class, "App/controllers");
        }
        if (class_exists($class)) {
            return $class;
        }
    }

}

?>
