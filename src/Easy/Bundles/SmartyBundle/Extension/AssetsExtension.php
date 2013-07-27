<?php

namespace Easy\Bundles\SmartyBundle\Extension;

use Easy\Bundles\SmartyBundle\Extension\Plugin\FunctionPlugin;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides integration of the Translation component with Smarty[Bundle].
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class AssetsExtension extends AbstractExtension
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlugins()
    {
        return array(
            new FunctionPlugin('asset', $this, 'assetUrlFunction'),
            new FunctionPlugin('stylesheet', $this, 'stylesheetFunction'),
            new FunctionPlugin('script', $this, 'scriptFunction')
        );
    }

    public function assetUrlFunction($path, \Smarty_Internal_Template $template)
    {
        return $this->container->get('helper.url')->content($path);
    }

    public function stylesheetFunction($path, \Smarty_Internal_Template $template)
    {
        return $this->container->get('helper.html')->stylesheet($path);
    }

    public function getScript($path, \Smarty_Internal_Template $template)
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
