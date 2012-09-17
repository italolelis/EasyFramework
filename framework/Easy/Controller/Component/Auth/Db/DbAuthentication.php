<?php

namespace Easy\Controller\Component\Auth\Db;

use Easy\Controller\Component\Auth\BaseAuthentication;
use Easy\Controller\Component\Auth\UserIdentity;
use Easy\Utility\Hash;
use Easy\Security\Sanitize;
use Easy\Utility\ClassRegistry;

class DbAuthentication extends BaseAuthentication
{

    public function authenticate($username, $password)
    {
        return $this->identify($username, $password);
    }

    /**
     * Indentyfies a user at the BD
     *
     * @param $securityHash string The hash used to encode the password
     * @return mixed The user model object
     */
    protected function identify($username, $password)
    {
        //clean the username field from SqlInjection
        $username = Sanitize::stripAll($username);
        $conditions = array_combine(array_values($this->fields), array($username));
        $conditions = Hash::merge($conditions, $this->conditions);

        $this->userProperties[] = 'password';
        $param = array(
            "fields" => $this->userProperties,
            "conditions" => $conditions
        );

        // Loads the user model class
        $userModel = ClassRegistry::load($this->userModel);
        // try to find the user
        $user = $userModel->getEntityManager()->find($param);
        if ($user) {
            // crypt the password written by the user at the login form
            if (!$this->hashEngine->check($password, $user->password)) {
                return false;
            }
            self::$_user = new UserIdentity();
            foreach ($user as $key => $value) {
                if (in_array($key, $this->userProperties)) {
                    self::$_user->{$key} = $value;
                }
            }
            return true;
        } else {
            return false;
        }
    }

}