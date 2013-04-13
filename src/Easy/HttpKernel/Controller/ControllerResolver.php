<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\Controller;

use Easy\HttpKernel\KernelInterface;
use Easy\Network\Request;
use Easy\Utility\Inflector;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class ControllerResolver implements ControllerResolverInterface
{

    private $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger A LoggerInterface instance
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request, KernelInterface $kernel)
    {
        $ctrlClass = $this->createControllerClass($request, $kernel);
        if (!$ctrlClass) {
            return false;
        }

        $reflection = new ReflectionClass($ctrlClass);
        if ($reflection->isAbstract() || $reflection->isInterface()) {
            $msg = __("The controller class %s is an interface or abstract class", $ctrlClass);

            if (null !== $this->logger) {
                $this->logger->error($msg);
            }

            throw new InvalidArgumentException($msg);
        }

        return $reflection->newInstanceWithoutConstructor();
    }

    /**
     * {@inheritdoc}
     */
    public function createControllerClass(Request $request, KernelInterface $kernel)
    {
        $controller = null;
        $bundleNamespace = $kernel->getActiveBundle()->getNamespace();

        if (!empty($request->params['controller'])) {
            $controller = Inflector::camelize($request->controller) . "Controller";
            $class = $bundleNamespace . "\Controller\\" . $controller;

            if (!class_exists($class)) {
                $msg = sprintf('Class "%s" does not exist.', $class);
                if (null !== $this->logger) {
                    $this->logger->error($msg);
                }
                throw new InvalidArgumentException($msg);
            }

            return $class;
        }

        return false;
    }

}
