<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 * @since         EasyFramework v 1.4
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Component', 'Controller');
App::uses('ObjectCollection', 'Collections/Generic');

/**
 * Components collection is used as a registry for loaded components and handles loading
 * and constructing component class objects.
 *
 * @package       Easy.Controller
 */
class ComponentCollection extends ObjectCollection {

    protected $controller = null;

    public function init(Controller &$controller) {
        if (empty($controller->components)) {
            return;
        }
        $this->controller = $controller;
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

}