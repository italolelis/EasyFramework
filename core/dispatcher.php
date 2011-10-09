<?php

/**
 *  Dispatcher é o responsável por receber os parâmetros passados ao EasyFramework
 *  através da URL, interpretá-los e direcioná-los para o respectivo controller.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
class Dispatcher extends Object {

    private $params;

    /**
     *  Chama o controller e a action solicitadas pela URL.
     * 
     *  @return mixed Instância do novo controller ou falso em caso de erro.
     */
    public function dispatch(Mapper $request) {

        $this->params = $request->getParams();
        $arquivo = APP_PATH . "controllers/" . $this->params['controller'] . "_controller.php";
        $this->params['id'] = isset($this->params['id']) ? $this->params['id'] : null;

        //Inclui o arquívo do controller
        if (file_exists($arquivo)) {
            require_once $arquivo;
        } else {
            $this->error('controller', $this->params);
        }

        //Formata a string para o formato CamelCase
        $controller_name = Inflector::camelize($this->params["controller"]) . "Controller";
        //Instancia o objeto requisitado
        $controller = & ClassRegistry::load($controller_name, "Controller");

        if (!method_exists($controller, $this->params['action']) && !App::path("View", "{$this->params['controller']}/{$this->params['action']}")) {
            $this->error('action', $this->params['action']);
            return false;
        }

        //Chama a ação do controller
        $controller->componentEvent("initialize");
        $controller->beforeFilter();
        $controller->componentEvent("startup");
        call_user_func(array($controller, $this->params['action']), $this->params['id']);
        if ($controller->autoRender) {
            $controller->display("{$this->params['controller']}/{$this->params['action']}");
        }
        $controller->componentEvent("shutdown");
        $controller->afterFilter();
    }

}

?>
