<?php

/**
 *  Mapper é o responsável por cuidar de URLs e roteamento dentro do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
class Mapper extends Object {

    /**
     * Constroi o caminho para qualquer controller
     * @param string $entity
     * @param string $ext
     * @return string 
     */
    public static function getControllerPath($entity, $ext=".php") {
        $entity = $entity . "_controller" . $ext;
        return "app/controllers/$entity";
    }

    /**
     * Constroi o caminho para a requisição de qualquer controller.
     * @param string $controller
     * @param string $action
     * @return string 
     */
    public static function getDispatchPath($controller, $action) {
        return $controller . "-" . $action;
    }

    /**
     * Constroi o caminho para qualquer arquivo do webroot
     * @param string $path
     * @return string 
     */
    public static function getWebrootPath($path) {
        return "app/webroot/$path";
    }

}

?>
