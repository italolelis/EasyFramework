<?php

namespace Easy\Controller\Component;

use Easy\Controller\Component;
use Easy\Controller\Controller;
use Easy\Annotations\AnnotationManager;

class AclComponent extends Component
{

    /**
     * The array of roles
     * @var array 
     */
    private $roles = array();

    /**
     * The array of users and their roles
     * @var array 
     */
    private $user;
    private $annotationManager;

    public function initialize(Controller $controller)
    {
        $this->controller = $controller;
        $this->annotationManager = new AnnotationManager("Authorized", $this->controller);
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
            $this->user[$user][] = $role;
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
        if ($this->roleExists($role) && isset($this->user[$user])) {
            unset($this->user[$user]);
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
            $userRole = $this->user[$user];
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
            if (!$this->isUserInRole($user, $role)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Gets a value indicating whether the specified role name already exists in the role data source.
     * @param string $role The name of the role to search for in the data source.
     * @return boolean true if the role name already exists in the data source; otherwise, false. 
     */
    public function roleExists($role)
    {
        return isset($this->roles[$role]);
    }

    /**
     * Adds a new role to the data source.
     * @param string $role The name of the role to create.
     */
    public function createRole($role)
    {
        $this->roles[$role] = $role;
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
        if (isset($this->user[$user])) {
            $roles = $this->user[$user];
            if (!is_array($roles)) {
                $roles = array($roles);
            }
            return $roles;
        }
    }

    public function hasAclAnnotation()
    {
        $action = $this->controller->request->action;
        return $this->annotationManager->hasAnnotation($action);
    }

    public function isAuthorized($user)
    {
        if ($this->hasAclAnnotation()) {
            $action = $this->controller->request->action;
            //Get the anotation object
            $roles = $this->annotationManager->getAnnotationObject($action);
            $roles = (array) $roles->roles;
            //If the requested method is in the permited array
            if (!$this->isUserInRoles($user, $roles)) {
                throw new UnauthorizedException(__("You can not access this."));
            }
        }
    }

}

