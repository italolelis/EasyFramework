<?php

App::uses('Session', 'Storage');
App::uses('Cookie', 'Storage');
App::uses('Set', 'Utility');
App::uses('Sanitize', 'Security');

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
     * @var boolean whether to enable cookie-based login. Defaults to false.
     */
    public $allowAutoLogin = false;
    public $autoCheck = true;

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
     * @var Model The user object
     */
    protected static $_user;

    /**
     * @var array Define the properties that you want to load in the session
     */
    protected $_userProperties = array('id', 'username', 'admin');

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
     * Inicializa o componente.
     *
     * @param Controller $controller object Objeto Controller
     * @return void
     */
    public function initialize(&$controller) {
        $this->controller = $controller;
    }

    /**
     * Faz as operações necessárias após a inicialização do componente.
     *
     * @param Controller $controller object Objeto Controller
     * @return void
     */
    public function startup(&$controller) {
        if ($this->autoCheck) {
            $this->checkAccess();
        }
    }

    /**
     * Checks if the user is logged and if has permission to access something
     */
    public function checkAccess() {
        if ($this->isAuthenticated()) {
            if (!Mapper::match($this->_loginAction)) {
                $this->_canAccess();
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
        return Session::check(self::$sessionKey);
    }

    /**
     * Redirect the user to the loggin page
     */
    private function _loginRedirect() {
        if (!Mapper::match($this->_loginAction)) {
            $this->controller->redirect($this->_loginRedirect);
        }
    }

    /**
     * Checks if the logged user is admin
     * @return bool
     */
    private function isAdmin() {
        return $this->getUser()->admin;
    }

    /**
     * Verify if the logged user can access some method
     * @throws NoPermissionException
     */
    private function _canAccess() {
        if (!$this->isAdmin()) {
            if ($this->_hasNoPermission()) {
                throw new NoPermissionException(__("You don't have permission to access this area."), array(
                    'title' => 'No Permission'
                ));
            }
        }
    }

    /**
     * Verify if the user which is not the admin has permission to access the
     * method
     *
     * @return bool True if hasn't permission, False if it has.
     */
    private function _hasNoPermission() {
        $annotation = new AnnotationManager("RolesNotAllowed", $this->controller);
        if ($annotation->hasClassAnnotation()) {
            return $annotation->hasClassAnnotation();
        } else if ($annotation->hasMethodAnnotation($this->controller->getRequest()->action)) {
            return $annotation->hasMethodAnnotation($this->controller->getRequest()->action);
        }
    }

    /**
     * Do the login process
     * @throws InvalidLoginException
     */
    public function authenticate($username, $password, $duration = 0) {
        if ($this->_identify($username, $password)) {
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
        //$password = Security::hash ( $password, Security::getHashType () );
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
     * Indentyfies a user at the BD
     *
     * @param $securityHash string The hash used to encode the password
     * @return mixed The user model object
     */
    private function _identify($username, $password) {
        // Loads the user model class
        $userModel = ClassRegistry::load($this->_userModel);
        // crypt the password written by the user at the login form
        $password = Security::hash($password);
        //clean the username field from SqlInjection
        $username = Sanitize::stripAll($username);

        $conditions = array_combine(array_values($this->_fields), array($username, $password));
        $conditions = Set::merge($conditions, $this->_conditions);

        $param = array(
            "fields" => $this->_userProperties,
            "conditions" => $conditions
        );
        // try to find the user
        return self::$_user = $userModel->find(Model::FIND_FIRST, $param);
    }

    /**
     * Create a session to the user
     *
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

?>
