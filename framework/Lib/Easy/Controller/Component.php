<?php

class Component {

    protected $controller = null;

    /**
     *  Callback executado antes de qualquer ação do controller.
     *
     *  @param object $controller
     *  @return true
     */
    public function initialize(Controller &$controller) {
        return $this->controller = $controller;
    }

    /**
     *  Callback executado após Controller::beforeFilter.
     *
     *  @param object $controller
     *  @return true
     */
    public function startup(Controller &$controller) {
        return $this->controller = $controller;
    }

    /**
     *  Callback executado após todas as ações do controller, mas antes de enviar
     *  a saída renderizada.
     *
     *  @param object $controller
     *  @return true
     */
    public function shutdown(Controller &$controller) {
        return $this->controller = $controller;
    }

}