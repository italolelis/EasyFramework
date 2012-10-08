<?php

namespace Easy\View\Helper;

use Easy\Routing\Mapper;

class UrlHelper extends AppHelper
{

    /**
     * Converts a virtual (relative) path to an application absolute path.
     * @param string $string The path to convert
     * @return string An absolute url to the path
     */
    public function content($string, $full = true)
    {
        return Mapper::url($string, $full);
    }

    /**
     * Generates a fully qualified URL to an action method by using the specified action name and controller name.
     * @param string $actionName The action Name
     * @param string $controllerName The controller Name
     * $param mixed $params The params to the action
     * @return string An absolute url to the action
     */
    public function action($actionName, $controllerName = null, $params = null, $area = true, $full = true)
    {
        if ($controllerName === true) {
            $controllerName = $this->view->getController()->getName();
            list(, $controllerName) = namespaceSplit($controllerName);
        }

        $url = array(
            'controller' => strtolower(urlencode($controllerName)),
            'action' => urlencode($actionName),
            $params
        );

        if ($this->view->getController()->getRequest()->prefix) {
            if ($area === true) {
                $area = strtolower($this->view->getController()->getRequest()->prefix);
                $url["prefix"] = $area;
                $url[$area] = true;
            }
        }

        return Mapper::url($url, $full);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getBase($full = true)
    {
        return Mapper::base($full);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getAreaBase($full = true)
    {
        if ($this->view->getController()->getRequest()->prefix) {
            $area = "/" . strtolower($this->view->getController()->getRequest()->prefix);
        } else {
            $area = null;
        }
        return Mapper::base($full) . $area;
    }

}