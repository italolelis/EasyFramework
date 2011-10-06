<?php

class SiteController extends Controller {

    function index() {
        //Verificamos se o usuário está logado
        isNotLogedIn();
        //Mostramos a view
        $this->view->display('index');
    }

    function home() {
        //Verifica se o usuário está logado
        isLogedIn();
        //Mostramos a view
        $this->view->display('home');
    }

}

?>