<?php

App::uses ( 'IComponent', 'Core/Controller' );
App::uses ( 'ObjectCollection', 'Core/Utility' );

class ComponentCollection extends ObjectCollection {
	
	protected $controller = null;
	
	public function init($controller) {
		if (empty ( $controller->components )) {
			return;
		}
		
		$this->controller = $controller;
		
		array_map ( array ($this, 'load' ), $controller->components );
	
	}
	
	/**
	 * Carrega todos os componentes associados ao controller.
	 *
	 * @return boolean Verdadeiro se todos os componentes foram carregados
	 */
	public function load($component, $options = array()) {
		$className = "{$component}Component";
		$class = ClassRegistry::load ( $className, "Component" );
		
		if (! is_null ( $class )) {
			$this->add ( $component, $class );
		} else {
			throw new MissingComponentException ( $className, array ('component' => $component ) );
		}
	}
	
	public function get($offset) {
		return $this->_loaded [$offset];
	}

}

?>