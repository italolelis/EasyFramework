<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\Bundle;

use Easy\HttpKernel\KernelInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class BundleGuesser
{

    protected $kernel;
    protected $request;

    function __construct(KernelInterface $kernel, Request $request)
    {
        $this->kernel = $kernel;
        $this->request = $request;
    }

    public function getBundle()
    {
        $className = strstr($this->request->attributes->get('_controller'), "::", true);

        $bundle = $this->getBundleForClass($className);

        while ($bundleName = $bundle->getName()) {
            if (null === $parentBundleName = $bundle->getParent()) {
                $bundleName = $bundle->getName();

                break;
            }
            $bundles = $this->kernel->getBundle($parentBundleName, false);
            $bundle = array_pop($bundles);
        }

        return $bundle;
    }

    /**
     * Returns the Bundle instance in which the given class name is located.
     *
     * @param  string $class  A fully qualified controller class name
     * @param  Bundle $bundle A Bundle instance
     * @throws InvalidArgumentException
     */
    protected function getBundleForClass($class)
    {
        $reflectionClass = new ReflectionClass($class);
        $bundles = $this->kernel->getBundles();

        do {
            $namespace = $reflectionClass->getNamespaceName();
            foreach ($bundles as $bundle) {
                if (0 === strpos($namespace, $bundle->getNamespace())) {
                    return $bundle;
                }
            }
            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass);

        throw new InvalidArgumentException(sprintf('The "%s" class does not belong to a registered bundle.', $class));
    }

}
