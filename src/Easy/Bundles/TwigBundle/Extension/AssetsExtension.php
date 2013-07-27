<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Bundles\TwigBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_Function_Method;

/**
 * Twig extension for Easy assets helper
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class AssetsExtension extends Twig_Extension
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'asset' => new Twig_Function_Method($this, 'getAssetUrl'),
            'stylesheet' => new Twig_Function_Method($this, 'getStylesheet'),
            'script' => new Twig_Function_Method($this, 'getScript')
        );
    }

    /**
     * Returns the public path of an asset.
     *
     * Absolute paths (i.e. http://...) are returned unmodified.
     *
     * @param string $path        A public path
     *
     * @return string A public path which takes into account the base path and URL path
     */
    public function getAssetUrl($path)
    {
        return $this->container->get('helper.url')->content($path);
    }

    public function getStylesheet($path)
    {
        return $this->container->get('helper.html')->stylesheet($path);
    }

    public function getScript($path)
    {
        return $this->container->get('helper.html')->script($path);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'assets';
    }

}
