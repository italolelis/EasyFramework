<?php

namespace Easy\Controller\Component\Auth\Db;

use Easy\Controller\Component\Auth\BaseAuthentication;
use Easy\Controller\Component\Auth\UserIdentity;
use Easy\Model\Conditions;
use Easy\Model\EntityManager;
use Easy\Model\Query;
use Easy\Security\Sanitize;
use Easy\Utility\Hash;

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

        $em = new EntityManager();
        $query = new Query();
        $query->select($this->userProperties)
                ->where(new Conditions($conditions));
        // try to find the user
        $user = $em->find($this->userModel, $query);
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