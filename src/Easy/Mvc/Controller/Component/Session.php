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

namespace Easy\Mvc\Controller\Component;

use Easy\Mvc\Controller\Component;
use Easy\Storage;

/**
 * The EasyFw Session component provides a way to persist client data between 
 * page requests. It acts as a wrapper for the `$_SESSION` as well as providing 
 * convenience methods for several `$_SESSION` related functions.
 *
 * @since 0.10
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Session extends Component
{

    /**
     * @var Storage\Session\Session 
     */
    private $session;

    public function __construct()
    {
        $this->session = new Storage\Session\Session();
        if (!$this->session->isStarted()) {
            $this->session->start();
        }
    }

    /**
     * Used to write a value to a session key.
     *
     * In your controller: $this->Session->write('Controller.sessKey', 'session value');
     *
     * @param string $name The name of the key your are setting in the session.
     * 							This should be in a Controller.key format for better organizing
     * @param string $value The value you want to store in a session.
     * @return boolean Success  
     */
    public function write($name, $value = null)
    {
        return $this->session->set($name, $value);
    }

    /**
     * Used to read a session values for a key or return values for all keys.
     *
     * In your controller: $this->Session->read('Controller.sessKey');
     * Calling the method without a param will return all session vars
     *
     * @param string $name the name of the session key you want to read
     * @return mixed value from the session vars
     */
    public function read($name = null)
    {
        return $this->session->get($name);
    }

    /**
     * Deletes a value from session
     *
     * In your controller: $this->Session->delete('Controller.sessKey');
     *
     * @param string $name the name of the session key you want to delete
     * @return boolean true is session variable is set and can be deleted, false is variable was not set.
     */
    public function delete($name)
    {
        return $this->session->remove($name);
    }

    /**
     * Used to check if a session variable is set
     *
     * In your controller: $this->Session->has('Controller.sessKey');
     *
     * @param string $name the name of the session key you want to check
     * @return boolean true is session variable is set, false if not
     */
    public function has($name)
    {
        return $this->session->has($name);
    }

    /**
     * Used to set a session variable that can be used to output messages in the view.
     *
     * In your controller: $this->Session->setFlash('This has been saved');
     *
     * Additional params below can be passed to customize the output, or the Message.[key].
     * You can also set additional parameters when rendering flash messages. See SessionHelper::flash()
     * for more information on how to do that.
     *
     * @param string $message Message to be flashed
     * @param string $key Message key, default is 'flash'
     * @return void
     */
    public function setFlash($message, $key = 'flash')
    {
        $this->session->getFlashBag()->add($key, $message);
    }

    /**
     * Used to renew a session id
     *
     * In your controller: $this->Session->renew();
     *
     * @return void
     */
    public function renew()
    {
        return $this->session->migrate();
    }

    /**
     * Used to destroy sessions
     *
     * In your controller: $this->Session->destroy();
     *
     * @return void
     */
    public function destroy()
    {
        return $this->session->invalidate();
    }

    /**
     * Sets Session id
     *
     * @param string $id
     * @return string
     */
    public function setId($id = null)
    {
        return $this->session->setId($id);
    }

    /**
     * Returns Session id
     *
     * @return string
     */
    public function getId()
    {
        return $this->session->getId();
    }

    /**
     * Returns a bool, whether or not the session has been started.
     *
     * @return boolean
     */
    public function started()
    {
        return $this->session->isStarted();
    }

    public function setLocale($locale)
    {
        return $this->session->set('_locale', $locale);
    }

    public function getLocale()
    {
        return $this->session->get('_locale');
    }

}