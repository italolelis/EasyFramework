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

namespace Easy\Network\Controller;

use Easy\Core\App;
use Easy\Network\Request;
use Easy\Utility\Inflector;
use InvalidArgumentException;
use ReflectionClass;

class ControllerResolver implements IControllerResolver
{

    private $controllerNamespace = 'Controller';

    public function getController(Request $request, $projectConfigs)
    {
        $ctrlClass = $this->createController($request);
        if (!$ctrlClass) {
            return false;
        }

        $reflection = new ReflectionClass($ctrlClass);
        if ($reflection->isAbstract() || $reflection->isInterface()) {
            throw new InvalidArgumentException(__("The controller class %s is an interface or abstract class", $ctrlClass));
        }
        return $reflection->newInstance($request, $projectConfigs);
    }

    /**
     * Load controller and return controller classname
     *
     * @param $request Request The request object
     * @return string controller class name
     */
    protected function createController(Request $request)
    {
        $controller = null;

        if (!empty($request->params['prefix'])) {
            $this->controllerNamespace = 'Areas/' . Inflector::camelize($request->params['prefix']) . "/Controller";
        }

        if (!empty($request->params['controller'])) {
            $controller = Inflector::camelize($request->controller);
        }

        if ($controller) {
            return App::classname($controller, $this->controllerNamespace, 'Controller');
        }

        return false;
    }

}
