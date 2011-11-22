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
    public static function dispatch($request =null) {
        //Pegamos os parametros da URL
        $request = self::normalize($request);
        //Importamos e instânciamos o controller
        $class = Inflector::camelize($request['controller']) . 'Controller';
        $controller = Controller::load($class, true);

        echo $controller->callAction($request);
    }

    protected static function normalize($request) {
        if (is_null($request)) {
            $request = Mapper::parse();
        }

        $request['controller'] = Inflector::hyphenToUnderscore($request['controller']);
        $request['action'] = Inflector::hyphenToUnderscore($request['action']);

        return $request;
    }

}

?>
