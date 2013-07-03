<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security\Authorization;

use Easy\Bundles\EasySecurityBundle\Metadata\AuthMetadata;
use Easy\Collections\Collection;
use Easy\Collections\Dictionary;
use Easy\HttpKernel\Exception\UnauthorizedHttpException;
use Easy\Security\Authentication\AuthenticationInterface;

/**
 * The Access Control List feature
 * 
 * @since 1.5
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Acl
{

    /**
     * @var AuthenticationInterface 
     */
    private $auth;

    /**
     * The array of roles
     * @var Collection 
     */
    private $roles;

    /**
     * The array of users and their roles
     * @var Dictionary 
     */
    private $users;

    /**
     * The metadata object
     * @var AuthMetadata;
     */
    protected $metadata;

    /**
     * @var string 
     */
    protected $field = "email";

    public function __construct()
    {
        $this->users = new Dictionary();
        $this->roles = new Collection();
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function setMetadata(AuthMetadata $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Gets a list of all the roles for the application.
     * @return array A string array containing the names of all the roles stored in the data source for the application.
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles(Collection $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Adds the specified user to the specified role.
     * @param string $user
     * @param string $role 
     */
    public function addUserToRole($user, $role)
    {
        if ($this->roleExists($role)) {
            $this->users->add($user, (array) $role);
        }
    }

    /**
     * Adds the specified user to the specified roles.
     * @param string $user
     * @param array $roles 
     */
    public function addUserToRoles($user, array $roles)
    {
        foreach ($roles as $role) {
            $this->addUserToRole($user, $role);
        }
    }

    /**
     * Removes the specified user from the specified role.
     * @param string $user
     * @param string $role 
     */
    public function removeUserFromRole($user, $role)
    {
        if ($this->roleExists($role) && $this->users->contains($user)) {
            $this->users->remove($user);
        }
    }

    /**
     * Removes the specified user from the specified roles.
     * @param string $user
     * @param array $roles 
     */
    public function removeUserFromRoles($user, array $roles)
    {
        foreach ($roles as $role) {
            $this->removeUserFromRole($user, $role);
        }
    }

    /**
     * Gets a value indicating whether a user is in the specified role.
     * @param string $user The name of the user to search for.
     * @param string $role The name of the role to search in.
     * @return boolean true if the specified user is in the specified role; otherwise, false.
     */
    public function isUserInRole($user, $role)
    {
        if ($this->roleExists($role)) {
            $userRole = $this->users->getItem($user);
            return in_array($role, $userRole);
        }
    }

    /**
     * Gets a value indicating whether a user is in the specified roles.
     * @param string $user The name of the user to search for.
     * @param array $roles The names of the roles to search in.
     * @return boolean true if the specified user is in the specified role; otherwise, false.
     */
    public function isUserInRoles($user, array $roles)
    {
        foreach ($roles as $role) {
            if ($this->isUserInRole($user, $role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets a value indicating whether the specified role name already exists in the role data source.
     * @param string $role The name of the role to search for in the data source.
     * @return boolean true if the role name already exists in the data source; otherwise, false. 
     */
    public function roleExists($role)
    {
        return $this->roles->contains($role);
    }

    /**
     * Adds a new role to the data source.
     * @param string $role The name of the role to create.
     */
    public function createRole($role)
    {
        $this->roles->add($role);
    }

    /**
     * Adds a new roles to the data source.
     * @param array $roles The names of the roles to create.
     */
    public function createRoles(array $roles)
    {
        foreach ($roles as $role) {
            $this->createRole($role);
        }
    }

    /**
     * Gets a list of the roles that a user is in.
     * @param string $user The user to return a list of roles for.
     * @return array A string array containing the names of all the roles that the specified user is in. 
     */
    public function getRolesForUser($user)
    {
        if ($this->users->contains($user)) {
            $roles = $this->users->getItem($user);
            if (!is_array($roles)) {
                $roles = array($roles);
            }
            return new Collection($roles);
        }
    }

    public function isAuthorized($user, $allowedRoles)
    {
        //If the requested method is in the permited array
        if ($allowedRoles !== null) {
            if (!$this->isUserInRoles($user, $allowedRoles)) {
                throw new UnauthorizedHttpException(__("You can not access this."));
            }
        }
        return true;
    }

    /**
     * Gets the authentication field
     * @return string The name of the field that will be used to authenticate
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Sets the authentication field
     * @param string $field The name of the field that will be used to authenticate
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Gets the IAuthentication object
     * @return AuthenticationInterface
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Sets the IAuthentication object
     * @param AuthenticationInterface $auth
     * @return Acl
     */
    public function setAuth(AuthenticationInterface $auth)
    {
        $this->auth = $auth;
        return $this;
    }

}

