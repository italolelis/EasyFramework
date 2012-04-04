<?php

App::uses('Component', 'Controller');
App::uses('ObjectCollection', 'Utility');

class ComponentCollection extends ObjectCollection {

    protected $controller = null;

    public function init(Controller $controller) {
        if (empty($controller->components)) {
            return;
        }

        $this->controller = $controller;

        array_map(array($this, 'load'), $controller->components);
    }

    /**
     * Carrega todos os componentes associados ao controller.
     * 
     * @return boolean Verdadeiro se todos os componentes foram carregados
     */
    public function load($component, $options = array()) {
        $className = "{$component}Component";
        $class = ClassRegistry::load($className, "Component");

        if (!is_null($class)) {
            $this->add($component, $class);
        } else {
            throw new MissingComponentException(null, array(
                'component' => $component,
                'title' => 'Component not found'
            ));
        }
    }

    public function get($offset) {
        return isset($this->_loaded [$offset]) ? $this->_loaded [$offset] : null;
    }

}

?>