<?php

App::import('Model', array('agendamento/agendamentoDAO.class', 'agendamento/agendamento.class'));

/**
 * Description of front_controller
 *
 * @author Italo
 */
class AgendamentoController extends Controller {

    private $sessao;

    function __construct() {
        //Precisamos invocar o construtor da classe mãe
        parent::__construct();
        //Verificamos se o usuário está logado
        isLogedIn();
        //Pegamos o objeto da sessão
        $this->sessao = Session::read('usuarios');
        $this->init(); //Chamamos a rotina que constroi o objeto dao
    }

    private function init() {
        //Verificamos o que foi passado no Array
        $id = (int) isset($_POST['id']) ? $_POST['id'] : null;
        $data = isset($_POST['data']) ? $_POST['data'] : null;
        $hora = isset($_POST['hora']) ? $_POST['hora'] : null;
        $servicos = isset($_POST['servicos']) ? $_POST['servicos'] : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;
        $cliente = isset($_POST['cliente']) ? $_POST['cliente'] : null;

        //Criamos um objeto de Agendamento
        $agendamento = new Agendamento($id, $data, $hora, $servicos, $status, $cliente);
        //Criamos um objeto DAO
        $this->model = new AgendamentoDAO($agendamento);
    }

    function index() {
        if ($this->sessao['admin']) {
            //Pegamos a lista de todos os agendamentos feitos por todos os usuários
            $lista_agendamento = $this->model->getAll();
            //Passamos a lista de agendamentos para a view
            $this->view->set('lista_agendamento', $lista_agendamento);
            //Mostra a view
            $this->view->display('admin/agendamentos');
        } else {
            //Pegamos a lista de todos os agendamentos feitos pelo usuário logado
            $lista_agendamento = $this->model->getAllByUserId($this->sessao['id']);
            //Passamos a lista de agendamentos para a view
            $this->view->set('lista_agendamento', $lista_agendamento);
            //Mostra a view
            $this->view->display('agendamentos');
        }
    }

    function addAgendamento() {
        //Pegamos a lista de serviços existentes
        $servicos = $this->getServicosList();
        //Passamos a lista de serviços para a view
        $this->view->set('servicos', $servicos);
        //Mostra a view
        $this->view->display('addAgendamento');
    }

    /**
     * Chama a página de edição dos projetos
     * @return View 
     */
    function editarAgendamento() {
        $id = (int) isset($_GET['id']) ? $_GET['id'] : null;

        //Pegamos o agendamento pelo ID
        $agendamento = $this->model->getById($id);
        //Criamos um array a partir da string de serviços que o usuário selecionou
        $servicos_usuario = explode(",", $agendamento->getServicos());
        //Construimos um array com os nomes dos serviços cadastrados
        $servicos = $this->buildList($this->getServicosList());

        //Passamos o objeto agendamento para a view
        $this->view->set('agendamento', $agendamento);
        //Passamos a lista de serviços para a view
        $this->view->set('servicos', $servicos);
        $this->view->set('servicos_usuario', $servicos_usuario);

        //Mostra a view
        $this->view->display('editarAgendamento');
    }

    function showCalendar() {
        if ($this->sessao['admin']) {
            //Pegamos a lista de todos os agendamentos feitos por todos os usuários
            $lista_agendamento = $this->model->getAll();
        } else {
            //Pegamos a lista de todos os agendamentos feitos pelo usuário logado
            $lista_agendamento = $this->model->getAllByUserId($this->sessao['id']);
        }

        //Criamos um array que conterá todas as datas agendadas
        $datas = array();

        //Para cada agendamento na lista de agendamentos
        foreach ($lista_agendamento as $agendamento) {
            //Pegamos a data do agendamento e criamos um array
            $date = explode("/", $agendamento->getDia());
            //Pegamos a hora do agendamento e criamos um array
            $time = explode(":", $agendamento->getHora());
            //Tiramos o zero a esquerda do mês do agendamento
            $date[1] = str_pad($date[1], 1) - 1;
            //Criamos o javascript para a view
            $datas[] = "{   
                            id: '{$agendamento->getId()}',
                            title: '{$agendamento->getStatus()}',
                            start: new Date({$date[2]}, {$date[1]}, {$date[0]}, {$time[0]}, {$time[1]}),
                            end: new Date({$date[2]}, {$date[1]}, {$date[0]}, {$time[0]}+1, {$time[1]}),
                            allDay: false
                            
                        },";
        }
        //Passamos o array de datas para a view
        $this->view->set('datas', $datas);

        $this->view->display('calendario');
    }

    /**
     * Retorna uma lista de tipos de serviços
     * @return array 
     */
    private function getServicosList() {
        //Importa os arquivos da model dos serviços
        App::import('Model', array('servicos/servicoDAO.class', 'servicos/servico.class'));

        $servicos = new ServicoDAO();   //delcaramos um novo objeto DAO
        return $servicos->getAll();     //Retornamos a lista de servicos cadastrados
    }

    private function buildList($list) {
        //Declaramos o array que conterá os nomes dos serviços
        $servicosNomes = array();
        //Para cada serviço na lista
        foreach ($list as $servico) {
            //Pega o nome e armazena no array
            $servicosNomes[] = $servico->getNome();
        }

        return $servicosNomes; //Retornamos o array
    }

}

?>
