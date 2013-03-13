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
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

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

        return $reflection->newInstance($request, $kernel);
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
