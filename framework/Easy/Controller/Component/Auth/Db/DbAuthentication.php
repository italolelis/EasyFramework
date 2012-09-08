<?php

namespace Easy\Controller\Component\Auth\Db;

use Easy\Controller\Component\Auth\BaseAuthentication;
use Easy\Controller\Component\Auth\UserIdentity;
use Easy\Utility\Hash;
use Easy\Security\Sanitize;
use Easy\Model\EntityManager;
use Easy\Utility\ClassRegistry;

class DbAuthentication extends BaseAuthentication
{

    public function authenticate($username, $password)
    {
        return $this->_identify($username, $password);
    }

    /**
     * Indentyfies a user at the BD
     *
     * @param $securityHash string The hash used to encode the password
     * @return mixed The user model object
     */
    protected function _identify($username, $password)
    {
        //clean the username field from SqlInjection
        $username = Sanitize::stripAll($username);
        $conditions = array_combine(array_values($this->_fields), array($username));
        $conditions = Hash::merge($conditions, $this->_conditions);

        $this->_userProperties[] = 'password';
        $param = array(
            "fields" => $this->_userProperties,
            "conditions" => $conditions
        );

        // Loads the user model class
        $userModel = ClassRegistry::load($this->_userModel);
        $entity = new EntityManager();
        $entity->setModel($userModel);
        // try to find the user
        $user = $entity->find($param);
        if ($user) {
            // crypt the password written by the user at the login form
            if (!static::check($password, $user->password)) {
                return false;
            }
            self::$_user = new UserIdentity();
            foreach ($user as $key => $value) {
                if (in_array($key, $this->_userProperties)) {
                    self::$_user->{$key} = $value;
                }
            }
            return true;
        } else {
            return false;
        }
    }

}