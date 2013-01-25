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

namespace Easy\Mvc\Controller\Component\Auth;

use Easy\Mvc\Controller\Component;
use Easy\Mvc\Controller\Component\Auth\Metadata\AuthMetadata;
use Easy\Mvc\Controller\Component\Auth\UserIdentity;
use Easy\Mvc\Controller\Component\Cookie;
use Easy\Mvc\Controller\Component\Exception\UnauthorizedException;
use Easy\Mvc\Controller\Component\Session;
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\Controller\Event\StartupEvent;
use Easy\Mvc\Model\ORM\EntityManager;
use Easy\Mvc\Routing\Mapper;
use Easy\Security\IAuthentication;
use Easy\Security\IHash;
use Easy\Security\Sanitize;
use Easy\Utility\Hash;
use InvalidArgumentException;

/**
 * The Db authentication class
 *
 * @since 1.6
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class DbAuthentication extends Component implements IAuthentication
{

    /**
     * @var array Fields to used in query, this represent the columns names to query
     */
    protected $fields = 'email';

    /**
     * @var array Extra conditions to find the user
     */
    protected $conditions = array();

    /**
     * @var string The User model to connect with the DB.
     */
    protected $userModel = null;

    /**
     * @var Easy\Security\UserIdentity The user object
     */
    protected static $user;

    /**
     * The hash engine object
     * @var IHash 
     */
    protected $hashEngine;

    /**
     * @var Session The Session Component
     */
    protected $session;

    /**
     * @var Cookie The Cookie Component
     */
    public $cookie;

    /**
     * @var bool whether to enable cookie-based login. Defaults to false.
     */
    public $allowAutoLogin = false;

    /**
     * @var bool whether to enable cookie-based login. Defaults to false.
     */
    public $autoCheck = true;

    /**
     * @var string
     */
    public static $sessionKey = 'Auth.User';

    /**
     * @var array Define the properties that you want to load in the session
     */
    protected $userProperties = array('id', 'email', 'role');

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
     * @var string The Message to be shown when the user can't login
     */
    protected $loginError = null;

    /**
     * @var bool 
     */
    protected $guestMode = false;

    public function __construct($hash)
    {
        $this->hashEngine = new $hash();
        if (!$this->hashEngine instanceof IHash) {
            throw new InvalidArgumentException(__("The hash engine must implement IHash interface."));
        }
    }

    /**
     * Faz as operações necessárias após a inicialização do componente.
     *
     * @param Controller $controller object Objeto Controller
     * @return void
     */
    public function startup(StartupEvent $event)
    {
        if ($this->autoCheck) {
            $this->controller = $event->getController();

            $request = $this->controller->getRequest();
            $url = Mapper::normalize($request->url);
            $loginAction = Mapper::normalize($this->loginAction);

            if ($loginAction != $url && $this->getGuestMode()) {
                return true;
            }

            $urlComponent = $this->controller->getContainer()->get("Url");
            if ($loginAction == $url) {
                if ($this->isAuthenticated()) {
                    return $this->controller->redirect($urlComponent->create($this->loginRedirect));
                }
                return true;
            }

            if (!$this->isAuthenticated()) {
                if (!$this->restoreFromCookie()) {
                    return $this->controller->redirect($urlComponent->create($loginAction));
                } else {
                    return $this->controller->redirect($urlComponent->create($this->loginRedirect));
                }
            }
        }
    }

    public function getAllowAutoLogin()
    {
        return $this->allowAutoLogin;
    }

    public function setAllowAutoLogin($allowAutoLogin)
    {
        $this->allowAutoLogin = $allowAutoLogin;
        return $this;
    }

    public function getAutoCheck()
    {
        return $this->autoCheck;
    }

    public function setAutoCheck($autoCheck)
    {
        $this->autoCheck = $autoCheck;
        return $this;
    }

    /**
     * Sets the guest mode
     * @param bool $guestMode
     */
    public function setGuestMode($guestMode)
    {
        $this->guestMode = $guestMode;
    }

    /**
     * Gets the login error message
     * @return the $loginError
     */
    public function getLoginError()
    {
        return $this->loginError;
    }

    /**
     * Sets the login error message
     * @param $loginError
     */
    public function setLoginError($loginError)
    {
        $this->loginError = $loginError;
    }

    /**
     * Gets the login redirect location
     * @return string
     */
    public function getLoginRedirect()
    {
        return $this->loginRedirect;
    }

    /**
     * Sets the login redirect location
     * @param string $loginRedirect
     */
    public function setLoginRedirect($loginRedirect)
    {
        $this->loginRedirect = $loginRedirect;
    }

    /**
     * Gets the logout redirect location
     * @return string
     */
    public function getLogoutRedirect()
    {
        return $this->logoutRedirect;
    }

    /**
     * Sets the logout redirect location
     * @param string $logoutRedirect
     */
    public function setLogoutRedirect($logoutRedirect)
    {
        $this->logoutRedirect = $logoutRedirect;
    }

    /**
     * Gets the login action to perfom the login
     * @return string The name of the action
     */
    public function getLoginAction()
    {
        return $this->loginAction;
    }

    /**
     * Sets the login action to perfom the login
     * @param string $loginAction The name of the action
     */
    public function setLoginAction($loginAction)
    {
        $this->loginAction = $loginAction;
    }

    public function getCookie()
    {
        return $this->cookie;
    }

    public function setCookie(Cookie $cookie)
    {
        $this->cookie = $cookie;
        return $this;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    public function getHashEngine()
    {
        return $this->hashEngine;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($_fields)
    {
        $this->fields = $_fields;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function setConditions($_conditions)
    {
        $this->conditions = $_conditions;
    }

    public function getUserModel()
    {
        return $this->userModel;
    }

    public function setUserModel($userModel)
    {
        $this->userModel = $userModel;
    }

    public function getUserProperties()
    {
        return $this->userProperties;
    }

    public function setUserProperties($_userProperties)
    {
        $this->userProperties = $_userProperties;
    }

    /**
     * Gets the guest mode based on annotations and state
     * @return type
     */
    public function getGuestMode()
    {
        //If has the @Guest annotation can access the action
        $metadata = new AuthMetadata($this->controller);
        if ($metadata->isGuest($this->controller->request->action)) {
            $this->guestMode = true;
        }
        return $this->guestMode;
    }

    public function getUser()
    {
        if (empty(static::$user) && !$this->session->has(static::$sessionKey)) {
            return null;
        }
        if (!empty(static::$user)) {
            $user = static::$user;
        } else {
            $user = $this->session->read(static::$sessionKey);
        }
        return $user;
    }

    public function authenticate($username, $password)
    {
        //clean the username field from SqlInjection
        $username = Sanitize::stripAll($username);
        $conditions = array_combine(array($this->fields), array($username));
        $conditions = Hash::merge($conditions, $this->conditions);

        $this->userProperties[] = 'password';
        // try to find the user
        $user = EntityManager::getInstance()->findOneBy($this->userModel, $conditions);
        if ($user) {
            // crypt the password written by the user at the login form
            if (!$this->hashEngine->check($password, $user->password)) {
                throw new UnauthorizedException($this->loginError);
            }
            unset($user->password);
            static::$user = new UserIdentity();
            foreach ($this->userProperties as $property) {
                if (isset($user->{$property})) {
                    static::$user->{$property} = $user->{$property};
                }
            }

            $this->setState();
            if ($this->allowAutoLogin) {
                $this->saveToCookie($username, $password, "2 Years");
            }
            // Returns the login redirect
            return $this->loginRedirect;
        } else {
            throw new UnauthorizedException($this->loginError);
        }
    }

    /**
     * Create a session to the user
     * @param $result mixed The query resultset
     */
    private function setState()
    {
        $this->session->write(static:: $sessionKey, static::$user);
    }

    /**
     * Saves necessary user data into a cookie.
     * This method is used when automatic login ({@link allowAutoLogin}) is enabled.
     * This method saves user ID, username, other identity states and a validation key to cookie.
     * These information are used to do authentication next time when user visits the application.
     * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
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

    public function logout()
    {
        // destroy the session
        $this->session->delete(static::$sessionKey);
        $this->session->destroy();
        // destroy the cookies
        $this->cookie->delete('ef');
        // redirect to login page
        return $this->logoutRedirect;
    }

    public function isAuthenticated()
    {
        $identity = $this->getUser();
        return !empty($identity);
    }

}