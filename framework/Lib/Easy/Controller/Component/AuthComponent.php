<?php

App::uses('Session', 'Storage');
App::uses('Cookie', 'Storage');
App::uses('UserIdentity', 'Component/Auth');

/**
 * AuthComponent é o responsável pela autenticação e controle de acesso na
 * aplicação.
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright 2011, EasyFramework
 *           
 */
class AuthComponent extends Component {

    /**
     * The permission Component
     * @var AclComponent 
     */
    private $Acl;

    /**
     * @var boolean whether to enable cookie-based login. Defaults to false.
     */
    public $allowAutoLogin = false;
    public $autoCheck = true;
    protected $guestMode = false;
    protected $authenticationType = 'Db';
    protected $engine = null;

    /**
     * @var array Fields to used in query, this represent the columns names to query
     */
    protected $_fields = array('username' => 'username', 'password' => 'password');

    /**
     * @var array Extra conditions to find the user
     */
    protected $_conditions = array();

    /**
     * @var string Login Controller ( The login page )
     */
    protected $_loginRedirect = null;

    /**
     * @var string Logout Controller ( The logout page )
     */
    protected $_logoutRedirect = null;

    /**
     * @var string Login Action (The login method)
     */
    protected $_loginAction = null;

    /**
     * @var string The User model to connect with the DB.
     */
    protected $_userModel = null;

    /**
     * @var UserIdentity The user object
     */
    protected static $_user;

    /**
     * @var array Define the properties that you want to load in the session
     */
    protected $_userProperties = array('id', 'username', 'role');

    /**
     * The session key name where the record of the current user is stored.
     * If unspecified, it will be "Auth.User".
     * @var string
     */
    public static $sessionKey = 'Auth.User';

    /**
     * @var string The Message to be shown when the user can't login
     */
    protected $_loginError = null;

    public function getUser() {
        if (empty(self::$_user) && !Session::check(self::$sessionKey)) {
            return null;
        }
        if (!empty(self::$_user)) {
            $user = self::$_user;
        } else {
            $user = Session::read(self::$sessionKey);
        }

        return $user;
    }

    public function getAcl() {
        return $this->Acl;
    }

    public function setAcl($acl) {
        $this->Acl = $acl;
    }

    public function getGuestMode() {
        return $this->guestMode;
    }

    public function setGuestMode($guestMode) {
        $this->guestMode = $guestMode;
    }

    public function getAutoCheck() {
        return $this->autoCheck;
    }

    public function setAutoCheck($autoCheck) {
        $this->autoCheck = $autoCheck;
    }

    public function getLoginRedirect() {
        return $this->_loginRedirect;
    }

    public function setLoginRedirect($loginRedirect) {
        $this->_loginRedirect = $loginRedirect;
    }

    public function getLogoutRedirect() {
        return $this->_logoutRedirect;
    }

    public function setLogoutRedirect($logoutRedirect) {
        $this->_logoutRedirect = $logoutRedirect;
    }

    public function getLoginAction() {
        return $this->_loginAction;
    }

    public function setLoginAction($loginAction) {
        $this->_loginAction = $loginAction;
    }

    public function getUserModel() {
        return $this->_userModel;
    }

    public function setUserModel($userModel) {
        $this->_userModel = $userModel;
    }

    public function getFields() {
        return $this->_fields;
    }

    public function setFields($fields) {
        $this->_fields = $fields;
    }

    public function getConditions() {
        return $this->_conditions;
    }

    public function addConditions($conditions) {
        $this->_conditions = $conditions;
    }

    public function getUserProperties() {
        return $this->_userProperties;
    }

    public function setUserProperties($userProperties) {
        $this->_userProperties = $userProperties;
    }

    /**
     *
     * @return the $loginError
     */
    public function getLoginError() {
        return $this->_loginError;
    }

    /**
     *
     * @param $loginError
     */
    public function setLoginError($loginError) {
        $this->_loginError = $loginError;
    }

    /**
     * loads the configured authentication objects.
     *
     * @return mixed either null on empty authenticate value, or an array of loaded objects.
     * @throws CakeException
     */
    public function getAuthEngine() {
        if (empty($this->authenticationType)) {
            return;
        }
        $authClass = Inflector::camelize($this->authenticationType . "Authentication");
        App::uses($authClass, 'Controller/Component/Auth/' . $this->authenticationType);

        if (!class_exists($authClass)) {
            throw new MissingAuthEngineException(__('Auth engine not found.'));
        }

        $obj = new $authClass();
        $obj->setUserModel($this->_userModel);
        $obj->setFields($this->_fields);
        $obj->setConditions($this->_conditions);
        $obj->setUserProperties($this->_userProperties);

        return $obj;
    }

    /**
     * Inicializa o componente.
     *
     * @param Controller $controller object Objeto Controller
     * @return void
     */
    public function initialize(&$controller) {
        $this->controller = $controller;
        $this->Acl = $this->Components->load('Acl');
    }

    /**
     * Faz as operações necessárias após a inicialização do componente.
     *
     * @param Controller $controller object Objeto Controller
     * @return void
     */
    public function startup(&$controller) {
        $this->engine = $this->getAuthEngine();

        if ($this->autoCheck) {
            if ($this->guestMode) {
                if ($this->Acl->hasAclAnnotation()) {
                    $this->checkAccess();
                }
            } else {
                $this->checkAccess();
            }
        }

        if ($this->getUser()) {
            //We need to serialize the Auth object
            $this->getUser()->setAuth(serialize($this));
        }
    }

    /**
     * Checks if the user is logged and if has permission to access something
     */
    public function checkAccess() {
        if ($this->isAuthenticated()) {
            if (!Mapper::match($this->_loginAction)) {
                $this->_canAccess($this->Acl);
            } else {
                $this->controller->redirect($this->_loginRedirect);
            }
        } elseif ($this->restoreFromCookie()) {
            //do something
        } else {
            $this->_loginRedirect();
        }
    }

    /**
     * Checks if the User is already logged
     *
     * @return bool
     */
    public function isAuthenticated() {
        $identity = $this->getUser();
        return !empty($identity);
    }

    /**
     * Redirect the user to the loggin page
     */
    private function _loginRedirect() {
        if (!Mapper::match($this->_loginAction)) {
            $this->controller->redirect($this->_loginAction);
        }
    }

    /**
     * Verify if the logged user can access some method
     */
    private function _canAccess(AclComponent $acl) {
        return $acl->isAuthorized($this->getUser()->username);
    }

    /**
     * Do the login process
     * @throws InvalidLoginException
     */
    public function authenticate($username, $password, $duration = 0) {
        if ($this->engine->authenticate($username, $password)) {
            self::$_user = &$this->engine->getUser();
            // Build the user session in the system
            $this->_setState();
            if ($this->allowAutoLogin) {
                $this->saveToCookie($username, $password, $duration);
            }
            // Returns the login redirect
            return $this->_loginRedirect;
        } else {
            throw new InvalidLoginException($this->_loginError);
        }
    }

    /**
     * Saves necessary user data into a cookie.
     * This method is used when automatic login ({@link allowAutoLogin}) is enabled.
     * This method saves user ID, username, other identity states and a validation key to cookie.
     * These information are used to do authentication next time when user visits the application.
     * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
     * @see restoreFromCookie
     */
    protected function saveToCookie($username, $password, $duration = null) {
        Cookie::write('ef', true, $duration);
        Cookie::write('c_user', $username, $duration);
        Cookie::write('token', $password, $duration);
    }

    protected function restoreFromCookie() {
        $identity = Cookie::read('ef');
        if (!empty($identity)) {
            $redirect = $this->authenticate(Cookie::read('c_user'), Cookie::read('token'));
            if ($this->isAuthenticated()) {
                return $this->controller->redirect($redirect);
            }
        }
        return null;
    }

    /**
     * Create a session to the user
     * @param $result mixed The query resultset
     */
    private function _setState() {
        Session::write(self::$sessionKey, self::$_user);
    }

    public function logout() {
        // destroy the session
        Session::delete(self::$sessionKey);
        Session::destroy();
        // destroy the cookies
        Cookie::delete('ef');
        Cookie::delete('c_user');
        Cookie::delete('token');
        // redirect to login page
        return $this->_logoutRedirect;
    }

}