<?php

App::uses('BaseAuthentication', 'Component/Auth');
App::uses('Hash', 'Utility');
App::uses('Sanitize', 'Security');

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
        // Loads the user model class
        $userModel = ClassRegistry::load($this->_userModel);
        // crypt the password written by the user at the login form
        $password = self::password($password);
        //clean the username field from SqlInjection
        $username = Sanitize::stripAll($username);

        $conditions = array_combine(array_values($this->_fields), array($username, $password));
        $conditions = Hash::merge($conditions, $this->_conditions);

        $param = array(
            "fields" => $this->_userProperties,
            "conditions" => $conditions
        );
        $entity = new EntityManager();
        $entity->setModel($userModel);
        // try to find the user
        $user = (array) $entity->find($param);

        if ($user) {
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