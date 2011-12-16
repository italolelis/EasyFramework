<?php

class Helper {

    protected $view;

    public function __construct($view) {
        $this->view = $view;
    }

    public function __get($helper) {
        return $this->view->{$helper};
    }

    public static function load($name) {

        if (!class_exists($name) && App::path("Helper", $name)) {
            App::import("Helper", $name);
        }
    }

}