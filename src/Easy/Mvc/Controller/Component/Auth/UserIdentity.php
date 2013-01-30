<?php

namespace Easy\Mvc\Controller\Component\Auth;

use Easy\Collections\Collection;
use Easy\Security\Identity\IPrincipal;

/**
 * Represents a Logged User 
 */
class UserIdentity implements IPrincipal
{

    /**
     * @var Collection 
     */
    private $roles;
    private $isAutenticated;

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set the roles for this IPrincipal
     * @param type $roles
     */
    public function setRoles(Collection $roles)
    {
        $this->roles = $roles;
    }

    /**
     * Sets if the current IPrincipal is authenticated
     * @param boolean $authenticated
     */
    public function setIsAuthenticated($authenticated)
    {
        $this->isAutenticated = $authenticated;
    }

    /**
     * Gets a value that indicates whether the user has been authenticated
     * @return boolean true if the user was authenticated; otherwise, false.
     */
    public function isAuthenticated()
    {
        return $this->isAutenticated;
    }

    /**
     * Checks if an IPrincipal object is guest
     * @return boolean
     */
    public function isGuest()
    {
        return !$this->isAuthenticated();
    }

    /**
     * Determines whether the current principal belongs to the specified role
     * @param string $role The name of the role for which to check membership
     * @return boolean true if the current principal is member of the specified role; otherwise false.
     */
    public function isInRole($role)
    {
        return $this->roles->contains($role);
    }

}