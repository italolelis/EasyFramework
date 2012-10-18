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

namespace Easy\View\Helper;

use Easy\Storage\Session;
use Easy\Utility\Hash;

/**
 * Session Helper.
 *
 * Session reading from the view.
 *
 * @package       Easy.View.Helper
 */
class SessionHelper extends AppHelper
{

    /**
     * Used to read a session values set in a controller for a key or return values for all keys.
     *
     * In your view: `$this->Session->read('Controller.sessKey');`
     * Calling the method without a param will return all session vars
     *
     * @param string $name the name of the session key you want to read
     * @return mixed values from the session vars
     */
    public function read($name = null)
    {
        return Session::read($name);
    }

    /**
     * Returns last error encountered in a session
     *
     * In your view: `$this->Session->error();`
     *
     * @return string last error
     */
    public function error()
    {
        return Session::error();
    }

    /**
     * Used to check is a session key has been set
     *
     * In your view: `$this->Session->check('Controller.sessKey');`
     *
     * @param string $name
     * @return boolean
     */
    public function check($name)
    {
        return Session::check($name);
    }

    /**
     * Used to render the message set in Controller::Session::setFlash()
     *
     * In your view: $this->Session->flash('somekey');
     * Will default to flash if no param is passed
     *
     * You can pass additional information into the flash message generation.  This allows you
     * to consolidate all the parameters for a given type of flash message into the view.
     *
     * {{{
     * echo $this->Session->flash('flash', array('params' => array('class' => 'new-flash')));
     * }}}
     *
     * The above would generate a flash message with a custom class name. Using $attrs['params'] you
     * can pass additional data into the element rendering that will be made available as local variables
     * when the element is rendered:
     *
     * {{{
     * echo $this->Session->flash('flash', array('params' => array('name' => $user['User']['name'])));
     * }}}
     *
     * This would pass the current user's name into the flash message, so you could create peronsonalized
     * messages without the controller needing access to that data.
     *
     * Lastly you can choose the element that is rendered when creating the flash message. Using
     * custom elements allows you to fully customize how flash messages are generated.
     *
     * {{{
     * echo $this->Session->flash('flash', array('element' => 'my_custom_element'));
     * }}}
     *
     * If you want to use an element from a plugin for rendering your flash message you can do that using the 
     * plugin param:
     *
     * {{{
     * echo $this->Session->flash('flash', array(
     * 		'element' => 'my_custom_element',
     * 		'params' => array('plugin' => 'my_plugin')
     * ));
     * }}}
     *
     * @param string $key The [Message.]key you are rendering in the view.
     * @param array $attrs Additional attributes to use for the creation of this flash message.
     *    Supports the 'params', and 'element' keys that are used in the helper.
     * @return string
     */
    public function flash($key = 'flash', array $attr = array())
    {
        $out = false;
        $attr = Hash::merge(array(
                    'class' => null,
                    'tag' => false
                        ), $attr
        );

        if (Session::check('Message.' . $key)) {
            $message = Session::read('Message.' . $key);

            if ($attr['tag']) {
                if (!is_array($message)) {
                    $message = array($message);
                }

                $out = "<div class='{$attr['class']}'>";
                foreach ($message as $value) {
                    $out .= "<p>" . $value . "</p>";
                };
                $out .= "</div>";
            } else {
                $out = $message;
            }
        }

        Session::delete('Message.' . $key);

        return $out;
    }

}
