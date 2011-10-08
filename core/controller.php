<?php

/**
 *  Controller permite que seja adicionada lógica a uma aplicação, além de prover
 *  funcionalidades básicas, como renderizaçao de views, redirecionamentos, acesso
 *  a modelos de dados, entre outros.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
abstract class Controller extends Object {

    /**
     * Propriedade que receberá uma classe que implementa a interface DAO
     * @var object 
     */
    protected $model;

    /**
     * Dados enviados pelo usuário via POST ou GET
     * @var mixed 
     */
    protected $data;

    /**
     * Propriedade que receberá um objeto View
     * @var View 
     */
    protected $view;

    /**
     * Define se a view será renderizada automaticamente
     * @var bool 
     */
    public $autoRender = true;

    /**
     * Layout utilizado para exibir a view
     * @var string 
     */
    public $layout = null;

    function __construct() {
        $this->view = new View();
        $this->data = array_merge_recursive($_POST, $_FILES);
    }

    /**
     * Mostra uma view
     * @param string $view o nome do template a ser exibido
     * @param string $ext a extenção do arquivo a ser exibido. O padrão é '.tpl'
     * @return View 
     */
    function display($view, $ext = ".tpl") {
        $this->view->layout = $this->layout;
        $this->view->autoRender = $this->autoRender;
        return $this->view->display($view);
    }

    /**
     * Define uma variável que será passada para a view
     * @param string $var o nome da variável que será passada para a view
     * @param mixed $value o valor da varíavel
     */
    function set($var, $value) {
        $this->view->set($var, $value);
    }

    /**
     *  Callback executado antes de qualquer aÃ§Ã£o do controller.
     *
     *  @return true
     */
    public function beforeFilter() {
        return true;
    }

    /**
     *  Callback executado antes da renderizaÃ§Ã£o de uma view.
     *
     *  @return true
     */
    public function beforeRender() {
        return true;
    }

    /**
     *  Callback executado apÃ³s as aÃ§Ãµes do controller.
     *
     *  @return true
     */
    public function afterFilter() {
        return true;
    }

}

?>
