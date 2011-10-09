<?php

/**
 *  AdminComponent é o responsável pela autenticação e controle de acesso na aplicação.
 * 
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
class AdminComponent extends Component {

    /**
     * Páginas que serão negadas para os usuários que não são administradores
     */
    public $permissions = array();

    /**
     *  Instância do controller.
     */
    public $controller;

    /**
     * Nome da sessão do usuário logado
     */
    public $sessionName = 'usuarios';

    /**
     * Sessão que será criada pelo componente
     */
    public $session;

    /**
     * Página de login
     */
    public $loginRedirect = 'usuarios-login';

    /**
     * Controller que realiza o login
     */
    public $loginAction = 'usuarios-login';

    /**
     *  Nome do modelo a ser utilizado para a autenticação.
     */
    public $userModel = "Usuarios";

    /**
     *  Inicializa o componente.
     *
     *  @param object $controller Objeto Controller
     *  @return void
     */
    public function initialize(&$controller) {
        //Inicializamos a sessão
        Session::start();
        $this->controller = $controller;
    }

    /**
     *  Faz as operações necessárias após a inicialização do componente.
     *
     *  @param object $controller Objeto Controller
     *  @return void
     */
    public function startup(&$controller) {
        
    }

    /**
     *  Finaliza o component.
     *
     *  @param object $controller Objeto Controller
     *  @return void
     */
    public function shutdown(&$controller) {
        
    }

    public function check() {
        if (Mapper::atual() != $this->loginAction) {
            if ($this->authenticate()) {
                $this->session = Session::read($this->sessionName);
                $this->canAccess();
            } else {
                $this->loginRedirect();
            }
        } else {
            if ($this->authenticate())
                $this->controller->redirect(Mapper::getRoot());
        }
    }

    public function authenticate() {
        return Session::started($this->sessionName);
    }

    public function loginRedirect() {
        if (Mapper::atual() != $this->loginRedirect) {
            $this->controller->redirect($this->loginRedirect);
        }
    }

    public function isAdmin() {
        return $this->session['admin'];
    }

    public function canAccess() {
        if (!$this->isAdmin()) {
            if (in_array(Mapper::atual(), $this->permissions)) {
                $this->error('permission');
            }
        }
    }

    /**
     *  Bloqueia os URLS para usuarios que não são administradores.
     *
     *  @param string $url URL a ser bloqueada
     *  @return void
     */
    public function deny($url = null) {
        if (is_array($url)) {
            foreach ($url as $u) {
                $this->permissions[] = $u;
            }
        } else {
            $this->permissions[] = $url;
        }
    }

    public function login() {
        $userModel = ClassRegistry::load($this->userModel);
        $password = Security::hash($this->controller->data['password'], 'md5');
        $param = array(
            "fields" => "id, username, admin",
            "conditions" => array("username = '{$this->controller->data['username']}' AND password = '{$password}'")
        );
        $result = $userModel->first($param);
        $this->buildSession($result);
    }

    public function buildSession($result) {
        if ($result) {
            $reg = array(
                'id' => $result['id'],
                'usuario' => $result['username'],
                'admin' => $result['admin'] === '1' ? true : false,
            );

            Session::write($this->sessionName, $reg);
        } else {
            throw new invalidLoginException("Usuário e senha incorretos");
        }
    }

    public function logout() {
        Session::delete($this->sessionName);
        Session::destroy();
        $this->loginRedirect();
    }

}

?>
