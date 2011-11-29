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
     *  Controller Object.
     */
    public $controller;

    /**
     * Session name
     */
    public $sessionName = 'usuarios';

    /**
     * Session Object
     */
    public $session;

    /**
     * Login Controller ( The login form )
     */
    public $loginRedirect = '/usuarios/login';

    /**
     * Login Action (The login call)
     */
    public $loginAction = '/usuarios/login';

    /**
     *  The User model to connect with the DB.
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

    /**
     * Checks if the user is logged and if has permission to access something
     */
    public function check() {
        if (Mapper::atual() !== $this->loginAction) {
            if ($this->authenticate()) {
                $this->session = Session::read($this->sessionName);
                $this->canAccess();
            } else {
                $this->loginRedirect();
            }
        } else {
            if ($this->authenticate())
                $this->controller->redirect("/" . Mapper::root());
        }
    }

    /**
     * Checks if the User is already logged
     * @return type 
     */
    public function authenticate() {
        return Session::started($this->sessionName);
    }

    /**
     * Redirect the user to the loggin page
     */
    public function loginRedirect() {
        if (Mapper::atual() !== $this->loginRedirect) {
            $this->controller->redirect($this->loginRedirect);
        }
    }

    /**
     * Checks if the logged user is admin
     * @return Boolean 
     */
    public function isAdmin() {
        return $this->session['admin'];
    }

    /**
     * Verify if the logged user can access some method
     */
    public function canAccess() {
        if (!$this->isAdmin()) {
            if ($this->hasNoPermission()) {
                throw new NoPermissionException('permission');
            }
        }
    }

    /**
     * Verify if the user which is not the admin has permission to access the method
     * @return Boolean True if hasn't permission, False if it has.
     */
    public function hasNoPermission() {
        $annotation = new AnnotationFactory("RolesNotAllowed", $this->controller);
        if ($annotation->hasClassAnnotation()) {
            return $annotation->hasClassAnnotation();
        } else if ($annotation->hasMethodAnnotation($this->controller->getLastAction())) {
            return $annotation->hasMethodAnnotation($this->controller->getLastAction());
        }
    }

    public function login($securityHash = "md5") {
        //Loads the user model class
        $userModel = ClassRegistry::load($this->userModel);
        //crypt the password written by the user at the login form
        $password = Security::hash($this->controller->data['password'], $securityHash);
        $param = array(
            "fields" => "id, username, admin",
            "conditions" => "username = '{$this->controller->data['username']}' AND BINARY password = '{$password}'"
        );
        $result = $userModel->first($param);
        //Build the user session in the system
        $this->buildSession($result);
    }

    /**
     * Create a session to the user
     * @param mixed $result The query resultset
     */
    public function buildSession($result) {
        if ($result) {
            $reg = array(
                'id' => $result->id,
                'usuario' => $result->username,
                'admin' => $result->admin === '1' ? true : false,
            );

            Session::write($this->sessionName, $reg);
        } else {
            throw new InvalidLoginException("Usuário e senha incorretos");
        }
    }

    public function logout() {
        Session::delete($this->sessionName);
        Session::destroy();
        $this->loginRedirect();
    }

}

?>
