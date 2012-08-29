<?php

namespace Easy\Controller\Component\Auth;

use Easy\Security\Identity\IPrincipal;

/**
 * Represents a Logged User 
 */
class UserIdentity implements IPrincipal
{

    /**
     * The AuthComponent object, which this User is related
     * @var AuthComponent 
     */
    private $auth;

    public function getAuth()
    {
        return $this->auth;
    }

    public function setAuth($auth)
    {
        $this->auth = $auth;
    }

    /**
     * Gets a value that indicates whether the user has been authenticated
     * @return boolean true if the user was authenticated; otherwise, false.
     */
    public function isAuthenticated()
    {
        return $this->auth->isAuthenticated();
    }

    public function isGuest()
    {
        return !$this->isAuthenticated();
    }

    /**
     * Determines whether the current principal belongs to the specified role
     * @param $role The name of the role for which to check membership
     * @return boolean true if the current principal is member of the specified role; otherwise false.
     */
    public function isInRole($role)
    {
        return $this->auth->getAcl()->isUserInRole($this->username, $role);
    }

}