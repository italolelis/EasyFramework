<?php

/**
 * Defines the basic funcionality of an identity object 
 */
interface IIdentity {

    /**
     * Gets a value that indicates whether the user has been authenticated
     * @return boolean true if the user was authenticated; otherwise, false.
     */
    public function isAuthenticated();
}
