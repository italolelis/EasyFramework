<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Mvc\Controller\Component;

use Easy\Core\App;
use Easy\Mvc\Controller\Component;
use Easy\Mvc\Controller\Component\Auth\Metadata\AuthMetadata;
use Easy\Mvc\Controller\Component\Auth\UserIdentity;
use Easy\Mvc\Controller\Component\Exception\UnauthorizedException;
use Easy\Mvc\Controller\ComponentCollection;
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\Routing\Mapper;
use Easy\Utility\Inflector;

/**
 * Auth component is responsable for authenticate and control each user logged in the application
 * 
 * @since 1.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Auth extends Component
{

    /**
     * The permission Component
     * @var Acl
     */
    private $Acl;

    /**
     * The Session Component
     * @var Session 
     */
    private $session;

    /**
     * The Cookie Component
     * @var Cookie 
     */
    private $cookie;

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
    protected $fields = array('username' => 'username');

    /**
     * @var array Extra conditions to find the user
     */
    protected $conditions = array();

    /**
     * @var string Login Controller ( The login page )
     */
    protected $loginRedirect = null;

    /**
     * @var string Logout Controller ( The logout page )
     */
    protected $logoutRedirect = null;

    /**
     * @var string Login Action (The login method)
     */
    protected $loginAction = null;

    /**
     * @var string The User model to connect with the DB.
     */
    protected $userModel = null;

    /**
     * @var UserIdentity The user object
     */
    protected static $user;

    /**
     * @var array Define the properties that you want to load in the session
     */
    protected $userProperties = array('id', 'username', 'role');

    /**
     * The session key name where the record of the current user is stored.
     * If unspecified, it will be "Auth.User".
     * @var string
     */
    public static $sessionKey = 'Auth.User';

    /**
     * @var string The Message to be shown when the user can't login
     */
    protected $loginError = null;

    public function __construct(ComponentCollection $components, $settings = array())
    {
        parent::__construct($components, $settings);
        $this->Acl = $this->Components->load('Acl');
        $this->session = $this->Components->load('Session');
        $this->cookie = $this->Components->load('Cookie');
    }

    /**
     * Gets the logged user
     * @return UserIdentity
     */
    public function getUser()
    {
        if (empty(self::$user) && !$this->session->check(self::$sessionKey)) {
            return null;
        }
        if (!empty(self::$user)) {
            $user = self::$user;
        } else {
            $user = $this->session->read(self::$sessionKey);
        }
        return $user;
    }

    /**
     * Gets the IAcl object for this IAuth
     * @return type
     */
    public function getAcl()
    {
        return $this->Acl;
    }

    public function setAcl($acl)
    {
        $this->Acl = $acl;
    }

    public function getGuestMode()
    {
        //If has the @Guest annotation can access the action
        $metadata = new AuthMetadata($this->controller);
        if ($metadata->isGuest($this->controller->request->action)) {
            $this->guestMode = true;
        }
        return $this->guestMode;
    }

    public function setGuestMode($guestMode)
    {
        $this->guestMode = $guestMode;
    }

    public function getAutoCheck()
    {
        return $this->autoCheck;
    }

    public function setAutoCheck($autoCheck)
    {
        $this->autoCheck = $autoCheck;
    }

    public function getLoginRedirect()
    {
        return $this->loginRedirect;
    }

    public function setLoginRedirect($loginRedirect)
    {
        $this->loginRedirect = $loginRedirect;
    }

    public function getLogoutRedirect()
    {
        return $this->logoutRedirect;
    }

    public function setLogoutRedirect($logoutRedirect)
    {
        $this->logoutRedirect = $logoutRedirect;
    }

    public function getLoginAction()
    {
        return $this->loginAction;
    }

    public function setLoginAction($loginAction)
    {
        $this->loginAction = $loginAction;
    }

    public function getUserModel()
    {
        return $this->userModel;
    }

    public function setUserModel($userModel)
    {
        $this->userModel = $userModel;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function addConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    public function getUserProperties()
    {
        return $this->userProperties;
    }

    public function setUserProperties($userProperties)
    {
        $this->userProperties = $userProperties;
    }

    /**
     *
     * @return the $loginError
     */
    public function getLoginError()
    {
        return $this->loginError;
    }

    /**
     *
     * @param $loginError
     */
    public function setLoginError($loginError)
    {
        $this->loginError = $loginError;
    }

    /**
     * loads the configured authentication objects.
     *
     * @return mixed either null on empty authenticate value, or an array of loaded objects.
     * @throws Error\MissingAuthEngineException
     */
    public function getAuthEngine()
    {
        if (empty($this->authenticationType)) {
            return;
        }
        $authClass = Inflector::camelize($this->authenticationType);
        $authClass = App::classname($authClass, 'Mvc\\Controller\\Component\\Auth\\' . $this->authenticationType, "Authentication");

        if (!class_exists($authClass)) {
            throw new Error\MissingAuthEngineException(array("engine" => $authClass));
        }

        $obj = new $authClass();
        $obj->setUserModel($this->userModel);
        $obj->setFields($this->fields);
        $obj->setConditions($this->conditions);
        $obj->setUserProperties($this->userProperties);

        return $obj;
    }

    /**
     * Inicializa o componente.
     *
     * @param Controller $controller object Objeto Controller
     * @return void
     */
    public function initialize(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Faz as operações necessárias após a inicialização do componente.
     *
     * @param Controller $controller object Objeto Controller
     * @return void
     */
    public function startup(Controller $controller)
    {
        $this->engine = $this->getAuthEngine();

        $request = $this->controller->request;
        $url = Mapper::normalize($request->url);
        $loginAction = Mapper::normalize($this->loginAction);

        if ($loginAction != $url && $this->getGuestMode()) {
            return true;
        }

        if ($loginAction == $url) {
            if ($this->isAuthenticated()) {
                return $this->controller->redirect($this->loginRedirect);
            }
            return true;
        }

        if (!$this->isAuthenticated()) {
            if (!$this->restoreFromCookie()) {
                return $this->controller->redirect($loginAction);
            }
        }

        if ($this->getUser() !== null) {
            $this->getUser()->setIsAuthenticated($this->isAuthenticated());
            $this->getUser()->setRoles($this->getAcl()->getRolesForUser($this->getUser()->username));
        }

        if (!$this->Acl->isAuthorized($this->getUser()->username)) {
            throw new UnauthorizedException(__("You can not access this."));
        }
    }

    /**
     * Checks if the User is already logged
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        $identity = $this->getUser();
        return !empty($identity);
    }

    /**
     * Do the login process
     * @throws Error\UnauthorizedException
     */
    public function authenticate($username, $password, $duration = null)
    {
        if ($this->engine->authenticate($username, $password)) {
            self::$user = $this->engine->getUser();
            // Build the user session in the system
            $this->_setState();
            if ($this->allowAutoLogin) {
                $this->saveToCookie($username, $password, $duration);
            }
            // Returns the login redirect
            return $this->loginRedirect;
        } else {
            throw new UnauthorizedException($this->loginError);
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
    protected function saveToCookie($username, $password, $duration = null)
    {
        $values = array(
            "c_user" => $username,
            "token" => $password
        );
        $this->cookie->write('ef', $values, $duration)
                ->create();
    }

    protected function restoreFromCookie()
    {
        $identity = $this->cookie->read('ef');
        if (!empty($identity)) {
            if ($this->authenticate($identity['c_user'], $identity['token'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create a session to the user
     * @param $result mixed The query resultset
     */
    private function _setState()
    {
        $this->session->write(self::$sessionKey, self::$user);
    }

    public function logout()
    {
        // destroy the session
        $this->session->delete(self::$sessionKey);
        $this->session->destroy();
        // destroy the cookies
        $this->cookie->delete('ef');
        // redirect to login page
        return $this->logoutRedirect;
    }

}