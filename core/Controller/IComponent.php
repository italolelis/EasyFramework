<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Italo
 */
interface IComponent {

    /**
     *  Callback executado antes de qualquer ação do controller.
     *
     *  @param object $controller
     *  @return true
     */
    public function initialize(&$controller);

    /**
     *  Callback executado após Controller::beforeFilter.
     *
     *  @param object $controller
     *  @return true
     */
    public function startup(&$controller);

    /**
     *  Callback executado após todas as ações do controller, mas antes de enviar
     *  a saída renderizada.
     *
     *  @param object $controller
     *  @return true
     */
    public function shutdown(&$controller);
}

?>
