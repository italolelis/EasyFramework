<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security\Authentication\Provider;

use Easy\Mvc\Controller\Component\Cookie;
use Easy\Mvc\Controller\Component\Exception\UnauthorizedException;
use Easy\Security\Authentication\AuthenticationInterface;
use Easy\Security\Authentication\Token\TokenInterface;
use Easy\Security\Authentication\UserIdentity;
use Easy\Security\HashInterface;
use Easy\Security\Sanitize;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * The Dao authentication class
 *
 * @since 1.6
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class DaoProvider implements AuthenticationInterface
{

    /**
     * @var array Fields to used in query, this represent the columns names to query
     */
    protected $fields = array('username' => 'email', 'password' => "password");

    /**
     * @var array Extra conditions to find the user
     */
    protected $conditions = array();

    /**
     * @var string The User model to connect with the DB.
     */
    protected $userModel = null;

    /**
     * @var UserIdentity The user object
     */
    protected static $user;

    /**
     * The hash engine object
     * @var HashInterface 
     */
    protected $hashEngine;

    /**
     * @var SessionInterface The Session
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
    protected $container;

    public function __construct($container, $hash)
    {
        $this->container = $container;
        if (is_string($hash)) {
            $this->hashEngine = new $hash();
        } else {
            $this->hashEngine = $hash;
        }

        if (!$this->hashEngine instanceof HashInterface) {
            throw new InvalidArgumentException(__("The hash engine must implement IHash interface."));
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

    public function setSession(SessionInterface $session)
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

    public function getUser()
    {
        if (empty(static::$user) && !$this->session->has(static::$sessionKey)) {
            return null;
        }
        if (!empty(static::$user)) {
            $user = static::$user;
        } else {
            $user = $this->session->get(static::$sessionKey);
        }
        return $user;
    }

    public function authenticate(TokenInterface $token)
    {
        //clean the username field from SqlInjection
        $username = Sanitize::stripAll($token->getUsername());
        $password = $token->getCredentials();
        $conditions = array_combine(array($this->fields['username']), array($username));
        $conditions = array_merge($conditions, $this->conditions);

        // try to find the user
        $em = $this->container->get('doctrine')->getManager();
        $user = $em->getRepository($this->userModel)->findOneBy($conditions);

        if ($user) {
            $accessor = PropertyAccess::createPropertyAccessor();

            // crypt the password written by the user at the login form
            if (!$this->hashEngine->check($password, $accessor->getValue($user, $this->fields['password']))) {
                throw new UnauthorizedException($this->loginError);
            }

            static::$user = new UserIdentity();

            foreach ($this->userProperties as $property) {
                static::$user->{$property} = $accessor->getValue($user, $property);
            }

            $this->setState();
            if ($this->allowAutoLogin) {
                $this->saveToCookie($token, "2 Years");
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
        $this->session->set(static::$sessionKey, static::$user);
    }

    /**
     * Saves necessary user data into a cookie.
     * This method is used when automatic login ({@link allowAutoLogin}) is enabled.
     * This method saves user ID, username, other identity states and a validation key to cookie.
     * These information are used to do authentication next time when user visits the application.
     * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
     */
    protected function saveToCookie(TokenInterface $token, $duration = null)
    {
        $values = array(
            "token" => serialize($token)
        );
        $this->cookie->write('ef', $values, $duration)
                ->create();
    }

    public function restoreFromCookie()
    {
        $identity = $this->cookie->read('ef');
        if (!empty($identity)) {
            if ($this->authenticate(unserialize($identity['token']))) {
                return true;
            }
        }
        return false;
    }

    public function logout()
    {
        // destroy the session
        $this->session->remove(static::$sessionKey);
        $this->session->save();
        session_write_close();
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