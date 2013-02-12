<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\HttpKernel;

use Easy\HttpKernel\Bundle\Bundle;
use Easy\HttpKernel\Bundle\BundleInterface;
use InvalidArgumentException;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * The Kernel is the heart of the Symfony system.
 *
 * It manages an environment made of bundles.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface KernelInterface extends HttpKernelInterface
{

    /**
     * Returns an array of bundles to registers.
     *
     * @return array An array of bundle instances.
     *
     * @api
     */
    public function registerBundles();

    /**
     * Gets the registered bundle instances.
     *
     * @return array An array of registered bundle instances
     *
     * @api
     */
    public function getBundles();

    /**
     * Returns a bundle and optionally its descendants by its name.
     *
     * @param string  $name  Bundle name
     * @param Boolean $first Whether to return the first bundle only or together with its descendants
     *
     * @return BundleInterface|Array A BundleInterface instance or an array of BundleInterface instances if $first is false
     *
     * @throws InvalidArgumentException when the bundle is not enabled
     *
     * @api
     */
    public function getBundle($name, $first = true);

    /**
     * Gets the active bundle, based on the request prefix
     * @return Bundle
     */
    public function getActiveBundle();

    /**
     * Loads the container configuration
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     *
     * @api
     */
    public function registerContainerConfiguration(LoaderInterface $loader);
}