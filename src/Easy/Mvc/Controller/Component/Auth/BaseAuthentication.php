<?php

namespace Easy\Mvc\Controller\Component\Auth;

use Easy\Security\HashFactory;

abstract class BaseAuthentication
{

    /**
     * @var array Fields to used in query, this represent the columns names to query
     */
    protected $fields = array('username' => 'username');

    /**
     * @var array Extra conditions to find the user
     */
    protected $conditions = array();

    /**
     * @var string The User model to connect with the DB.
     */
    protected $userModel = null;

    /**
     * @var Easy\Security\UserIdentity The user object
     */
    protected static $_user;

    /**
     * The hash engine object
     * @var \Easy\Security\IHash 
     */
    protected $hashEngine;

    /**
     * @var array Define the properties that you want to load in the session
     */
    protected $userProperties = array('id', 'username', 'role');

    public function __construct()
    {
        $factory = new HashFactory();
        $this->hashEngine = $factory->build();
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

    public function setUserModel($_userModel)
    {
        $this->userModel = $_userModel;
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
        return self::$_user;
    }

    public abstract function authenticate($username, $password);

    /**
     * Indentyfies a user at the BD
     *
     * @param $securityHash string The hash used to encode the password
     * @return mixed The user model object
     */
    protected abstract function identify($username, $password);
}