<?php

App::uses ( 'IComponent', 'Core/Controller' );

class ComponentCollection {
	
	protected $components = array ();
	protected $controller;
	
	public function init($controller) {
		$this->controller = $controller;
		if (! empty ( $this->controller->components )) {
			array_map ( array (
					$this, 
					'loadComponent' ), $this->controller->components );
		}
	}
	
	/**
	 * Carrega todos os componentes associados ao controller.
	 *
	 * @return boolean Verdadeiro se todos os componentes foram carregados
	 */
	public function loadComponent($component) {
		$className = "{$component}Component";
		$class = ClassRegistry::load ( $className, "Component" );
		
		if (! is_null ( $class )) {
			$this->add ( $component, $class );
		} else {
			throw new MissingComponentException ( $className, array (
					'component' => $component ) );
		}
	}
	
	public function fireEvent($event) {
		foreach ( $this->components as $component ) {
			if (method_exists ( $component, $event )) {
				$component->{$event} ( $this->controller );
			} else {
				trigger_error ( "O método {$event} não pode ser chamado na classe {$component}", E_USER_WARNING );
			}
		}
	}
	
	public function exists($offset) {
		return isset ( $this->components [$offset] );
	}
	
	public function get($offset) {
		return $this->components [$offset];
	
	}
	
	public function add($offset, $value) {
		return $this->components [$offset] = $value;
	}
	
	public function addRange($values = array()) {
		foreach ( $values as $key => $value ) {
			$this->components [$key] = $value;
		}
		
		return $this->components;
	}
	
	public function remove($offset) {
		unset ( $this->components [$offset] );
	}

}

?>