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

namespace Easy\Mvc\View\Helper;

/**
 * Session Helper.
 *
 * Session reading from the view.
 */
class SessionHelper
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
        
    }

    public function flash($key = 'flash', array $attr = array())
    {
        
    }

}
