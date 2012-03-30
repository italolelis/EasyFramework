<?php

class UrlHelper extends AppHelper {

    /**
     * Converts a virtual (relative) path to an application absolute path.
     * @param string $string
     * @return string 
     */
    public function content($string) {
        return Mapper::url($string, true);
    }

    /**
     * Generates a fully qualified URL to an action method by using the specified action name and controller name.
     * @param string $action
     * @param string $controller
     * @return string 
     */
    public function action($action, $controller = null, $params = null) {
        if (is_null($controller)) {
            $controller = strtolower($this->view->getController()->getName());
        }
        if (!empty($params)) {
            $params = (Array) $params;
            $params = "/" . implode('/', $params);
        }
        return Mapper::url("/" . $controller . "/" . $action . $params, true);
    }

    public function getBase() {
        return Mapper::base() === "/" ? Mapper::domain() : Mapper::domain() . Mapper::base();
    }

}