<?php

App::import('Model', array('exceptions', 'login/login.class'));

class LoginController {

    private $login;

    function __construct() {
        $user = isset($_POST['username']) ? $_POST['username'] : null;
        $pass = isset($_POST['password']) ? $_POST['password'] : null;

        $this->login = new Login($user, $pass);
    }

    function login() {
        try {
            $this->login->RealizarLogin();
        } catch (invalidLoginException $exc) {
            echo $exc->getMessage();
        }
    }

    function logout() {
        Login::RealizarLogoff();
        header("Location: site-index");
    }

}

?>
