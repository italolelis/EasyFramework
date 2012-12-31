<?php

namespace Easy\Security;

interface IAuthentication
{

    public function logout();

    public function isAuthenticated();

    public function getUser();

    public function authenticate($username, $password);
}