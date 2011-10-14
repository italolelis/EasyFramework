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
        //Pegamos os parametros da URL
        $params = $request->parse();
        //Importamos e instânciamos o controller
        $controller = $this->getController($request);

        //Chamamos o evento initialize dos componentes
        $controller->componentEvent("initialize");
        //Chamamos o evento beforeFilter dos controllers
        $controller->beforeFilter();
        //Chamamos o evento startup dos componentes
        $controller->componentEvent("startup");
        if (!method_exists($controller, $params['action']) && !App::path("View", "{$params['controller']}/{$params['action']}")) {
            $this->error("action", $params["action"]);
        } else {
            //Chama a ação do controller
            call_user_func_array(array(&$controller, $params["action"]), $params["params"]);
        }
        //Se o autorender está habilitado
        if ($controller->autoRender) {
            //Mostramos a view
            $controller->display("{$params["controller"]}/{$params["action"]}");
        }
        //Chamamos o evento shutdown dos componentes
        $controller->componentEvent("shutdown");
        //Chamamos o evento afterFilter dos controllers
        $controller->afterFilter();
    }

    function getController($request) {
        //Carregamos o arquívo do controller
        $controllerName = $this->loadController($request);
        //Instanciamos o controller
        if (class_exists($controllerName)) {
            return $controller = & ClassRegistry::load($controllerName, "Controller");
        }
    }

    function loadController($request) {
        //Montamos como vai ficar o arquívo para ser importado
        $file = strtolower($request->params['controller']) . "_controller";
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
