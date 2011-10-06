<?php

App::import('Lib', 'interfaces');

/**
 *  Controller permite que seja adicionada lógica a uma aplicação, além de prover
 *  funcionalidades básicas, como renderizaçao de views, redirecionamentos, acesso
 *  a modelos de dados, entre outros.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
abstract class Controller extends Object implements IAjaxCrud {

    /**
     * Propriedade que receberá uma classe que implementa a interface DAO
     * @var object 
     */
    protected $model;

    /**
     * View
     * @var View 
     */
    protected $view;

    function __construct() {
        $this->view = new View();
    }

    function add() {
        return $this->model->add();
    }

    function update() {
        return $this->model->update();
    }

    function delete() {
        
    }

}

?>
