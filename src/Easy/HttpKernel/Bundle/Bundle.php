<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\HttpKernel\Bundle;

use LogicException;
use ReflectionObject;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

abstract class Bundle implements BundleInterface
{

    /**
     * @var string 
     */
    protected $name;

    /**
     * @var ReflectionObject 
     */
    protected $reflected;
    protected $extension;

    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        
    }

    public function boot()
    {
        
    }

    public function shutdown()
    {
        
    }

    public function setContainer(ContainerInterface $container = null)
    {
        
    }

    /**
     * Returns the bundle's container extension.
     *
     * @return ExtensionInterface|null The container extension
     *
     * @api
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $basename = preg_replace('/Bundle$/', '', $this->getName());

            $class = $this->getNamespace() . '\\DependencyInjection\\' . $basename . 'Extension';
            if (class_exists($class)) {
                $extension = new $class();

                // check naming convention
                $expectedAlias = Container::underscore($basename);
                if ($expectedAlias != $extension->getAlias()) {
                    throw new LogicException(sprintf(
                            'The extension alias for the default extension of a ' .
                            'bundle must be the underscored version of the ' .
                            'bundle name ("%s" instead of "%s")', $expectedAlias, $extension->getAlias()
                    ));
                }

                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        if ($this->extension) {
            return $this->extension;
        }
    }

    /**
     * Gets the Bundle namespace.
     *
     * @return string The Bundle namespace
     *
     * @api
     */
    public function getNamespace()
    {
        if (null === $this->reflected) {
            $this->reflected = new ReflectionObject($this);
        }

        return $this->reflected->getNamespaceName();
    }

    /**
     * Gets the Bundle directory path.
     *
     * @return string The Bundle absolute path
     *
     * @api
     */
    public function getPath()
    {
        if (null === $this->reflected) {
            $this->reflected = new ReflectionObject($this);
        }

        return dirname($this->reflected->getFileName());
    }

    /**
     * Returns the bundle parent name.
     *
     * @return string The Bundle parent name it overrides or null if no parent
     *
     * @api
     */
    public function getParent()
    {
        return null;
    }

    /**
     * Returns the bundle name (the class short name).
     *
     * @return string The Bundle name
     *
     * @api
     */
    final public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $name = get_class($this);
        $pos = strrpos($name, '\\');

        return $this->name = false === $pos ? $name : substr($name, $pos + 1);
    }

}
