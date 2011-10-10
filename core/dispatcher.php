<?php

/**
 *  Dispatcher é o responsável por receber os parâmetros passados ao EasyFramework
 *  através da URL, interpretá-los e direcioná-los para o respectivo controller.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
class Dispatcher extends Object {

    /**
     *  Chama o controller e a action solicitadas pela URL.
     * 
     *  @return mixed Instância do novo controller ou falso em caso de erro.
     */
    public function dispatch(Mapper $request) {
        $params = $request->parse();
        $controller = $this->getController($request);


        $controller->componentEvent("initialize");
        $controller->beforeFilter();
        $controller->componentEvent("startup");
        if (!method_exists($controller, $params['action']) && !App::path("View", "{$params['controller']}/{$params['action']}")) {
            $this->error("action", $params["action"]);
        } else {
            //Chama a ação do controller
            call_user_func_array(array(&$controller, $params["action"]), $params["params"]);
        }

        if ($controller->autoRender) {
            $controller->display("{$params["controller"]}/{$params["action"]}");
        }
        $controller->componentEvent("shutdown");
        $controller->afterFilter();
    }

    function getController($request) {
        $controllerName = $this->loadController($request);
        //Instancia o controller
        if ($controllerName) {
            if (class_exists($controllerName)) {
                return $controller = & ClassRegistry::load($controllerName, "Controller");
            }
        } else {
            return false;
        }
    }

    function loadController($request) {
        $file = $request->params['controller'] . "_controller";
        //Inclui o arquívo do controller
        if (App::import("Controller", $file)) {
            //Formata a string para o formato CamelCase
            return Inflector::camelize($request->params["controller"]) . "Controller";
        } else {
            $this->error('controller', $request->params);
        }
    }

}

?>
