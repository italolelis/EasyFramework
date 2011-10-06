<?php

App::import('Model', array('exceptions', 'usuarios/usuario.class', 'usuarios/usuarioDAO.class'));

class UsuariosController extends Controller {

    private $sessao;

    function __construct() {
        //Verificamos se o usuário está logado
        parent::__construct();

        $this->sessao = Session::read('usuarios');

        $this->init();
    }

    private function init() {
        //Pegamos os dados via post
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $username = isset($_POST['username']) ? $_POST['username'] : null;
        $password = isset($_POST['password']) ? $_POST['password'] : null;
        $admin = isset($_POST['admin']) ? $_POST['admin'] : null;
        $nome = isset($_POST['nome']) ? $_POST['nome'] : null;
        $endereco = isset($_POST['endereco']) ? $_POST['endereco'] : null;
        $complemento = isset($_POST['complemento']) ? $_POST['complemento'] : null;
        $bairro = isset($_POST['bairro']) ? $_POST['bairro'] : null;
        $cidade = isset($_POST['cidade']) ? $_POST['cidade'] : null;
        $tel = isset($_POST['tel']) ? $_POST['tel'] : null;
        $cel = isset($_POST['cel']) ? $_POST['cel'] : null;
        $email = isset($_POST['email']) ? $_POST['email'] : null;

        try {
            $user = new Usuario($id, $username, $password, $admin, $nome, $endereco, $complemento, $bairro, $cidade, $tel, $cel, $email);
            $this->model = new UsuarioDAO($user);
        } catch (invalidPasswordException $ex) {
            die($ex->getMessage());
        }
    }

    function index() {
        isLogedIn();

        $result_usuarios = $this->model->getAll();
        $this->view->set('usuarios', $result_usuarios);
        $this->view->display('admin/usuarios');
    }

    function editarUsuario() {
        isLogedIn();

        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $usuario = $this->model->getById($id);

        $this->view->set('usuario', $usuario);

        if ($this->sessao['admin']) {
            $this->view->display('admin/editarUsuario');
        } else {
            $this->view->display('editarConta');
        }
    }

    function addUsuario() {
        $this->view->display('addUsuario');
    }

    function updatePassword() {
        isLogedIn();

        $id = isset($_GET['id']) ? $_GET['id'] : null;

        $this->view->set('id', $id);
        $this->view->display('updatePassword');
    }

    function add() {
        if ($this->sessao['admin'])
            $this->model->adminAdd();
        else
            $this->model->add();
    }

    function update() {
        if ($this->sessao['admin'])
            $this->model->adminUpdate();
        else
            $this->model->update();
    }

    function delete() {
        $this->model->delete();
    }

    function updatepass() {
        try {
            $this->model->alterarSenha();
        } catch (invalidPasswordException $exc) {
            echo $exc->getMessage();
        }
    }

}

?>
