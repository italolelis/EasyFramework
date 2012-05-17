<?php

App::uses('IIdentity', 'Security/Identity');

/**
 * Defines the basic functionality of a principal object. 
 */
interface IPrincipal extends IIdentity {

    /**
     * Determines whether the current principal belongs to the specified role
     * @param $role The name of the role for which to check membership
     * @return boolean true if the current principal is member of the specified role; otherwise false.
     */
    public function isInRole($role);
}
