<?php

App::uses('UserIdentity', 'Component/Auth');

abstract class BaseAuthentication {

    /**
     * @var array Fields to used in query, this represent the columns names to query
     */
    protected $_fields = array('username' => 'username', 'password' => 'password');

    /**
     * @var array Extra conditions to find the user
     */
    protected $_conditions = array();

    /**
     * @var string The User model to connect with the DB.
     */
    protected $_userModel = null;

    /**
     * @var UserIdentity The user object
     */
    protected static $_user;

    /**
     * @var array Define the properties that you want to load in the session
     */
    protected $_userProperties = array('id', 'username', 'role');

    public function getFields() {
        return $this->_fields;
    }

    public function setFields($_fields) {
        $this->_fields = $_fields;
    }

    public function getConditions() {
        return $this->_conditions;
    }

    public function setConditions($_conditions) {
        $this->_conditions = $_conditions;
    }

    public function getUserModel() {
        return $this->_userModel;
    }

    public function setUserModel($_userModel) {
        $this->_userModel = $_userModel;
    }

    public function getUserProperties() {
        return $this->_userProperties;
    }

    public function setUserProperties($_userProperties) {
        $this->_userProperties = $_userProperties;
    }

    /**
     * Hash a password with the application's salt value (as defined with Configure::write('Security.salt');
     *
     * This method is intended as a convenience wrapper for Security::hash().  If you want to use
     * a hashing/encryption system not supported by that method, do not use this method.
     *
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function password($password) {
        return Security::hash($password);
    }

    public function getUser() {
        return self::$_user;
    }

    public abstract function authenticate($username, $password);

    /**
     * Indentyfies a user at the BD
     *
     * @param $securityHash string The hash used to encode the password
     * @return mixed The user model object
     */
    protected abstract function _identify($username, $password);
}