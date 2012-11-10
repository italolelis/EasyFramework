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
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\Routing\Mapper;

/**
 * An easy way to deal with routes on controllers
 *
 * @since 1.7
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Url extends Component
{

    protected $prefix;

    /**
     * Inicializa o componente.
     *
     * @param Controller $controller object Objeto Controller
     * @return void
     */
    public function initialize(Controller $controller)
    {
        $this->controller = $controller;
        $this->prefix = strtolower($this->controller->getRequest()->prefix);
    }

    public function url($path, $full = true)
    {
        return Mapper::url($path, $full);
    }

    /**
     * Converts a virtual (relative) path to an application absolute path.
     * @param string $string The path to convert
     * @return string An absolute url to the path
     */
    public function content($path, $full = true)
    {
        $options = array();
        if (is_array($path)) {
            return $this->url($path, $full);
        }
        if (strpos($path, '://') === false) {
            if (!empty($options['pathPrefix']) && $path[0] !== '/') {
                $path = $options['pathPrefix'] . $path;
            }
            if (
                    !empty($options['ext']) &&
                    strpos($path, '?') === false &&
                    substr($path, -strlen($options['ext'])) !== $options['ext']
            ) {
                $path .= $options['ext'];
            }
            $path = h($this->webroot($path));

            if ($full) {
                $base = $this->url("/", true);
                $request = $this->controller->getRequest();
                $len = strlen($request["webroot"]);
                if ($len) {
                    $base = substr($base, 0, -$len);
                }
                $path = $base . $path;
            }
        }
        return $path;
    }

    /**
     * Generates a fully qualified URL to an action method by using the specified action name and controller name.
     * @param string $actionName The action Name
     * @param string $controllerName The controller Name
     * $param mixed $params The params to the action
     * @return string An absolute url to the action
     */
    public function create($actionName, $controllerName = null, $params = null, $area = true, $full = true)
    {
        if ($controllerName === true) {
            $controllerName = $this->controller->getName();
            list(, $controllerName) = namespaceSplit($controllerName);
        }

        $url = array(
            'controller' => strtolower(urlencode($controllerName)),
            'action' => urlencode($actionName),
            $params
        );

        if ($this->prefix) {
            if ($area === true) {
                $url["prefix"] = $this->prefix;
            }
        }

        return $this->url($url, $full);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getBase($full = true)
    {
        return $this->url("/", $full);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getAreaBase($full = true)
    {
        if ($this->prefix) {
            $area = "/" . strtolower($this->prefix);
        } else {
            $area = null;
        }
        return $this->getBase($full) . $area;
    }

}