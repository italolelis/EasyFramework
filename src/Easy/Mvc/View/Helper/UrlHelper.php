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

use Easy\Mvc\Controller\ControllerInterface;
use Easy\Mvc\Routing\Generator\IUrlGenerator;
use Easy\Mvc\Routing\Generator\UrlGenerator;
use Easy\Mvc\View\Helper;

class UrlHelper extends Helper implements IUrlGenerator
{

    public $request;

    /**
     * @var UrlGenerator
     */
    public $url;

    public function __construct(ControllerInterface $controller)
    {
        parent::__construct($controller);
        $this->request = $controller->getRequest();
        $this->url = new UrlGenerator($this->request, $controller->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function create($actionName, $controllerName = null, $params = null, $area = true, $referenceType = self::ABSOLUTE_URL)
    {
        return $this->url->create($actionName, $controllerName, $params, $area, $referenceType);
    }

    /**
     * Converts a virtual (relative) path to an application absolute path.
     * @param string $string The path to convert
     * @return string An absolute url to the path
     */
    public function content($path, $referenceType = self::ABSOLUTE_URL)
    {
        return $this->url->content($path, $referenceType);
    }

    /**
     * Checks if a file exists when theme is used, if no file is found default location is returned
     *
     * @param string $file The file to create a webroot path to.
     * @return string Web accessible path to file.
     */
    public function webroot($file)
    {
        $asset = explode('?', $file);
        $asset[1] = isset($asset[1]) ? '?' . $asset[1] : null;
        $webPath = "{$this->request["webroot"]}" . $asset[0];
        $file = $asset[0];
        if (strpos($webPath, '//') !== false) {
            return str_replace('//', '/', $webPath . $asset[1]);
        }
        return $webPath . $asset[1];
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
            $controllerName = $this->controller->getName();
            list(, $controllerName) = namespaceSplit($controllerName);
        }

        $url = array(
            'controller' => strtolower($controllerName),
            'action' => $actionName,
            $params
        );

        if ($this->request->prefix) {
            if ($area === true) {
                $area = strtolower($this->request->prefix);
                $url["prefix"] = $area;
            }
        }
        return $this->url->doCreate($url, $full);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getBase($referenceType = self::ABSOLUTE_URL)
    {
        return $this->url->getBase($referenceType);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getAreaBase($referenceType = self::ABSOLUTE_URL)
    {
        return $this->url->getAreaBase($referenceType);
    }

}