<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Mvc\Controller\Component\Auth\Db;

use Easy\Core\App;
use Easy\Core\Config;
use Easy\Mvc\Controller\Component\Auth\BaseAuthentication;
use Easy\Mvc\Controller\Component\Auth\UserIdentity;
use Easy\Mvc\Model\ORM\EntityManager;
use Easy\Security\Sanitize;
use Easy\Utility\Hash;

/**
 * The Db authentication class
 *
 * @since 1.6
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
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

        $em = new EntityManager(Config::read("datasource"), App::getEnvironment());
        // try to find the user
        $user = $em->findOneBy($this->userModel, $conditions);
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