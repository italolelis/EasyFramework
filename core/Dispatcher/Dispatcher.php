<?php

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
     *  Dispatch an HTTP request to a controller/action.
     * 
     *  @return mixed Instância do novo controller ou falso em caso de erro.
     */
    public static function dispatch() {
        //Parse the URL
        $request = Mapper::parse();
        //Create the controller class name
        $class = Inflector::camelize($request['controller']) . 'Controller';
        //Loads and instanciate the controller
        $controller = Controller::load($class, true);
        //Calls the action
        echo $controller->callAction($request);
    }

}

?>
