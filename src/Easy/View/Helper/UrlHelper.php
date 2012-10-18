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

use Easy\Routing\Mapper;

class UrlHelper extends AppHelper
{

    /**
     * Converts a virtual (relative) path to an application absolute path.
     * @param string $string The path to convert
     * @return string An absolute url to the path
     */
    public function content($string, $full = true)
    {
        return Mapper::url($string, $full);
    }

    /**
     * Generates a fully qualified URL to an action method by using the specified action name and controller name.
     * @param string $actionName The action Name
     * @param string $controllerName The controller Name
     * $param mixed $params The params to the action
     * @return string An absolute url to the action
     */
    public function action($actionName, $controllerName = null, $params = null, $area = true, $full = true)
    {
        if ($controllerName === true) {
            $controllerName = $this->view->getController()->getName();
            list(, $controllerName) = namespaceSplit($controllerName);
        }

        $url = array(
            'controller' => strtolower(urlencode($controllerName)),
            'action' => urlencode($actionName),
            $params
        );

        if ($this->view->getController()->getRequest()->prefix) {
            if ($area === true) {
                $area = strtolower($this->view->getController()->getRequest()->prefix);
                $url["prefix"] = $area;
                $url[$area] = true;
            }
        }

        return Mapper::url($url, $full);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getBase($full = true)
    {
        return Mapper::base($full);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getAreaBase($full = true)
    {
        if ($this->view->getController()->getRequest()->prefix) {
            $area = "/" . strtolower($this->view->getController()->getRequest()->prefix);
        } else {
            $area = null;
        }
        return Mapper::base($full) . $area;
    }

}