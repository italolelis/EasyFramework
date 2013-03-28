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

namespace Easy\Security\Authorization;

use Easy\Collections\Collection;
use Easy\Collections\Dictionary;
use Easy\Mvc\Controller\Component\Exception\UnauthorizedException;
use Easy\Mvc\Controller\ControllerAware;
use Easy\Security\Authentication\AuthenticationInterface;
use Easy\Security\Authentication\Metadata\AuthMetadata;

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
    protected $request;

    public function __construct()
    {
        $this->users = new Dictionary();
        $this->roles = new Collection();
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
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

    public function isAuthorized($user)
    {
        $action = $this->request->action;
        //Get the anotation object
        $roles = $this->metadata->getAuthorized($action);
        //If the requested method is in the permited array
        if ($roles !== null) {
            if (!$this->isUserInRoles($user, $roles)) {
                throw new UnauthorizedException(__("You can not access this."));
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

