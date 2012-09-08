<?php

namespace Easy\Controller\Component;

use Easy\Routing\Mapper;
use Easy\Controller\Controller;
use Easy\Controller\Component;

class UrlComponent extends Component
{

    protected $prefix;

    /**
     * Inicializa o componente.
     *
     * @param Controller $controller object Objeto Controller
     * @return void
     */
    public function initialize(Controller $controller)
    {
        $this->controller = $controller;
        $this->prefix = strtolower($this->controller->getRequest()->prefix);
    }

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
    public function create($actionName, $controllerName = null, $params = null, $area = true, $full = true)
    {
        if ($controllerName === true) {
            $controllerName = $this->controller->getName();
            list(, $controllerName) = namespaceSplit($controllerName);
        }

        $url = array(
            'controller' => strtolower(urlencode($controllerName)),
            'action' => urlencode($actionName),
            $params
        );

        if ($this->prefix) {
            if ($area === true) {
                $area = $this->prefix;
            }
            $url["prefix"] = $area;
            $url[$area] = true;
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
        if ($this->prefix) {
            $area = "/" . strtolower($this->prefix);
        } else {
            $area = null;
        }
        return Mapper::base($full) . $area;
    }

}