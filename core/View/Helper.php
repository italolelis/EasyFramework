<?php

class Helper {

    protected $view;

    public function __construct($view) {
        $this->view = $view;
    }

    public function __get($helper) {
        return $this->view->{$helper};
    }

    /**
     * Loads the helper file and instanciate
     * @param type $name
     * @param type $instance
     * @return \name 
     */
    public static function load($name, $view, $instance = true) {
        if (!class_exists($name) && App::path("Helper", $name)) {
            App::uses($name, "Helper");
        }

        if ($instance) {
            return new $name($view);
        }
    }

}