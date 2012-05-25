<?php

class UrlHelper extends AppHelper {

    /**
     * Converts a virtual (relative) path to an application absolute path.
     * @param string $string
     * @return string 
     */
    public function content($string, $full = true) {
        return Mapper::url($string, $full);
    }

    /**
     * Generates a fully qualified URL to an action method by using the specified action name and controller name.
     * @param string $action
     * @param string $controller
     * @return string 
     */
    public function action($action, $controller = null, $params = null) {
        if ($controller === true) {
            $controller = strtolower($this->view->getController()->getName());
        }
        return Mapper::url(array('controller' => urlencode($controller), 'action' => urlencode($action), $params), true);
    }

    public function getBase() {
        return Mapper::url();
    }

}