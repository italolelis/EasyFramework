<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 1.4
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Component', 'Controller');
App::uses('ObjectCollection', 'Collections/Generic');
App::uses('EventListener', 'Event');

/**
 * Components collection is used as a registry for loaded components and handles loading
 * and constructing component class objects.
 *
 * @package       Easy.Controller
 */
class ComponentCollection extends ObjectCollection implements EventListener {

    protected $_controller = null;

    /**
     * Get the controller associated with the collection.
     *
     * @return Controller.
     */
    public function getController() {
        return $this->_Controller;
    }

    public function init(Controller &$controller) {
        if (empty($controller->components)) {
            return;
        }
        $this->_controller = $controller;
        foreach ($controller->components as $name) {
            $this->load($name);
        }
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
            $obj = $this->add($component, $class);
            return $obj;
        } else {
            throw new MissingComponentException(null, array(
                'component' => $component,
                'title' => 'Component not found'
            ));
        }
    }

    /**
     * Returns the implemented events that will get routed to the trigger function
     * in order to dispatch them separately on each component
     *
     * @return array
     */
    public function implementedEvents() {
        return array(
            'Controller.initialize' => array('callable' => 'trigger'),
            'Controller.startup' => array('callable' => 'trigger'),
            'Controller.beforeRender' => array('callable' => 'trigger'),
            'Controller.beforeRedirect' => array('callable' => 'trigger'),
            'Controller.shutdown' => array('callable' => 'trigger'),
        );
    }

}