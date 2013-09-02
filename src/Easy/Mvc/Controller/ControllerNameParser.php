<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller;

use Easy\HttpKernel\KernelInterface;
use InvalidArgumentException;

class ControllerNameParser
{

    protected $kernel;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Converts a short notation a:b:c to a class::method.
     *
     * @param string $controller A short notation controller (a:b:c)
     *
     * @return string A string with class::method
     *
     * @throws InvalidArgumentException when the specified bundle is not enabled
     *                                   or the controller cannot be found
     */
    public function parse($controller)
    {
        if (3 != count($parts = explode(':', $controller))) {
            throw new InvalidArgumentException(sprintf('The "%s" controller is not a valid a:b:c controller string.', $controller));
        }

        list($bundle, $controller, $action) = $parts;
        $controller = str_replace('/', '\\', $controller);
        $bundles = array();

        // this throws an exception if there is no such bundle
        foreach ($this->kernel->getBundle($bundle, false) as $b) {
            $try = $b->getNamespace() . '\\Controller\\' . $controller . 'Controller';
            if (class_exists($try)) {
                return $try . '::' . $action . 'Action';
            }

            $bundles[] = $b->getName();
            $msg = sprintf('Unable to find controller "%s:%s" - class "%s" does not exist.', $bundle, $controller, $try);
        }

        if (count($bundles) > 1) {
            $msg = sprintf('Unable to find controller "%s:%s" in bundles %s.', $bundle, $controller, implode(', ', $bundles));
        }

        throw new InvalidArgumentException($msg);
    }

}