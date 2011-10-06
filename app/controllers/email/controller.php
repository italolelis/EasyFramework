<?php

class EmailController {

    function confirm() {
        App::import('Model', 'email/email');

        $emailModel = new Email($_POST);
        if ($emailModel->SendEmail())
            require_once Mapper::getViewPath('email/confirmacao');
    }

    function index() {
        App::import('View', 'index');
    }

}

?>