<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\HttpKernel\Controller;

use Easy\HttpKernel\KernelInterface;
use Easy\Network\Request;
use Easy\Utility\Inflector;
use ReflectionClass;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class ControllerResolver implements IControllerResolver
{

    public function getController(Request $request, KernelInterface $kernel)
    {
        $ctrlClass = $this->createController($request, $kernel);
        if (!$ctrlClass) {
            return false;
        }

        $reflection = new ReflectionClass($ctrlClass);
        if ($reflection->isAbstract() || $reflection->isInterface()) {
            throw new InvalidArgumentException(__("The controller class %s is an interface or abstract class", $ctrlClass));
        }
        return $reflection->newInstance($request, $kernel);
    }

    /**
     * Load controller and return controller classname
     *
     * @param $request Request The request object
     * @return string controller class name
     */
    protected function createController(Request $request, KernelInterface $kernel)
    {
        $controller = null;
        $bundleNamespace = $kernel->getActiveBundle()->getNamespace();

        if (!empty($request->params['controller'])) {
            $controller = Inflector::camelize($request->controller) . "Controller";
            $class = $bundleNamespace . "\Controller\\" . $controller;
            if (!class_exists($class)) {
                throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
            }
            return $class;
        }

        return false;
    }

}
