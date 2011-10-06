<?php

App::import('Model', array('servicos/servicoDAO.class', 'servicos/servico.class'));

/**
 * Description of front_controller
 *
 * @author Italo
 */
class ServicosController extends Controller {

    function __construct() {
        //Verificamos se o usuário está logado
        isLogedIn();
        //Precisamos invocar o construtor da classe mãe
        parent::__construct();
        //Se existir algo no array $_POST
        $this->init(); //Chamamos a rotina que constroi o objeto dao
    }

    private function init() {
        //Verificamos o que foi passado no Array
        $id = (int) isset($_POST['id']) ? $_POST['id'] : null;
        $nome = isset($_POST['nome']) ? $_POST['nome'] : null;
        //Criamos um objeto de Agendamento
        $servico = new Servico($id, $nome);
        //Criamos um objeto DAO
        $this->model = new ServicoDAO($servico);
    }

    function index() {
        //Pegamos a lista de todos os agendamentos feitos por todos os usuários
        $list = $this->model->getAll();
        //Passamos a lista de agendamentos para a view
        $this->view->set('lista_servicos', $list);
        //Mostra a view
        $this->view->display('admin/servicos');
    }

    function addServico() {
        //Mostra a view
        $this->view->display('admin/addServico');
    }

    /**
     * Chama a página de edição dos projetos
     * @return View 
     */
    function editarServico() {
        //Pegamos o id do agendamento
        $id = isset($_GET['id']) ? $_GET['id'] : null;

        //Pegamos o agendamento pelo ID
        $servico = $this->model->getById($id);

        //Passamos o objeto agendamento para a view
        $this->view->set('servico', $servico);

        //Mostra a view
        $this->view->display('admin/editarServico');
    }

}

?>
