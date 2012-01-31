<?php

App::uses('Session', 'Core/Storage');

/**
 *  AuthComponent é o responsável pela autenticação e controle de acesso na aplicação.
 * 
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
class AuthComponent implements IComponent {

    public $autoCheck = true;

    /**
     *  Controller Object.
     */
    protected $controller;

    /**
     * Login Controller ( The login form )
     */
    protected $loginRedirect = '/usuarios/login';

    /**
     * Login Action (The login call)
     */
    protected $loginAction = '/usuarios/login';

    /**
     *  The User model to connect with the DB.
     */
    protected $userModel = "Usuarios";

    /**
     *  The user object
     */
    protected $user;

    /**
     * The session key name where the record of the current user is stored.  If
     * unspecified, it will be "Auth.User".
     *
     * @var string
     */
    public static $sessionKey = 'Auth.User';

    public function getUser() {
        if (!Session::check(self::$sessionKey)) {
            return null;
        }
        return Session::read(self::$sessionKey);
    }

    public function getAutoCheck() {
        return $this->autoCheck;
    }

    public function setAutoCheck($autoCheck) {
        $this->autoCheck = $autoCheck;
    }

    public function getLoginRedirect() {
        return $this->loginRedirect;
    }

    public function setLoginRedirect($loginRedirect) {
        $this->loginRedirect = $loginRedirect;
    }

    public function getLoginAction() {
        return $this->loginAction;
    }

    public function setLoginAction($loginAction) {
        $this->loginAction = $loginAction;
    }

    public function getUserModel() {
        return $this->userModel;
    }

    public function setUserModel($userModel) {
        $this->userModel = $userModel;
    }

    /**
     *  Inicializa o componente.
     *
     *  @param object $controller Objeto Controller
     *  @return void
     */
    public function initialize(&$controller) {
        $this->controller = $controller;
    }

    /**
     *  Faz as operações necessárias após a inicialização do componente.
     *
     *  @param object $controller Objeto Controller
     *  @return void
     */
    public function startup(&$controller) {
        if ($this->autoCheck) {
            $this->check();
        }
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
        if ($this->isLoggedIn()) {
            if (!Mapper::match($this->loginAction)) {
                $this->canAccess();
            } else {
                $this->controller->redirect("/" . Mapper::getRoot());
            }
        } else {
            $this->loginRedirect();
        }
    }

    /**
     * Checks if the User is already logged
     * @return type 
     */
    private function isLoggedIn() {
        return Session::check(self::$sessionKey);
    }

    /**
     * Redirect the user to the loggin page
     */
    private function loginRedirect() {
        if (!Mapper::match($this->loginRedirect)) {
            $this->controller->redirect($this->loginRedirect);
        }
    }

    /**
     * Checks if the logged user is admin
     * @return Boolean 
     */
    private function isAdmin() {
        return $this->getUser()->admin;
    }

    /**
     * Verify if the logged user can access some method
     */
    private function canAccess() {
        if (!$this->isAdmin()) {
            if ($this->hasNoPermission()) {
                throw new NoPermissionException("You don't have permission to access this area.");
            }
        }
    }

    /**
     * Verify if the user which is not the admin has permission to access the method
     * @return Boolean True if hasn't permission, False if it has.
     */
    private function hasNoPermission() {
        $annotation = new AnnotationManager("RolesNotAllowed", $this->controller);
        if ($annotation->hasClassAnnotation()) {
            return $annotation->hasClassAnnotation();
        } else if ($annotation->hasMethodAnnotation($this->controller->request->action)) {
            return $annotation->hasMethodAnnotation($this->controller->request->action);
        }
    }

    /**
     * Do the login process
     */
    public function login($username, $password, $args = array()) {
        $args = array_merge(
                array(
            'securityHash' => 'md5',
            'cookies' => false
                ), $args);

        if ($this->identify($username, $password, $args)) {
            //Build the user session in the system
            $this->buildSession();
        } else {
            throw new InvalidLoginException("Usuário e senha incorretos");
        }
    }

    /**
     * Indentyfies a user at the BD
     * @param string $securityHash The hash used to encode the password
     * @return mixed The user model object 
     */
    private function identify($username, $password, $args) {
        //Loads the user model class
        $userModel = ClassRegistry::load($this->userModel);
        //crypt the password written by the user at the login form
        $password = Security::hash($password, $args['securityHash']);
        $param = array(
            "fields" => "id, username, admin",
            "conditions" => "username = '{$username}' AND BINARY password = '{$password}'"
        );
        //try to fund the user
        $this->user = $userModel->first($param);

        return $this->user;
    }

    /**
     * Create a session to the user
     * @param mixed $result The query resultset
     */
    private function buildSession() {
        Session::write(self::$sessionKey, $this->user);
    }

    public function logout() {
        //destroy the session
        Session::delete(self::$sessionKey);
        Session::destroy();
        //redirect to login page
        $this->loginRedirect();
    }

}

?>
