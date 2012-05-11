<?php

class UserIdentity {

    private $acl;

    public function __construct() {
    }

    public function getAcl() {
        return $this->acl;
    }

    public function setAcl($auth) {
        $this->acl = $auth;
    }

    public function isAuthenticated() {
        //return $this->auth->isAuthenticated();
    }

    public function isInRole($role) {
        //return $this->acl->isUserInRole($this->username, $role);
    }

}

