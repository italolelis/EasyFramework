<?php

namespace Easy\Bundles\SmartyBundle\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Provides integration of the Routing component with Smarty[Bundle].
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class RoutingExtension extends AbstractExtension
{

    protected $generator;

    /**
     * Constructor.
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlugins()
    {
        return array(
            new Plugin\BlockPlugin('url', $this, 'getUrl_block'),
            new Plugin\ModifierPlugin('url', $this, 'getUrl_modifier')
        );
    }

    public function getUrl_block(array $parameters = array(), $name = null, $template, &$repeat)
    {
        //only output on the closing tag
        if (!$repeat) {
            return $this->generator->generate($name, $parameters, false);
        }
    }

    public function getUrl_modifier($name, array $parameters = array())
    {
        return $this->generator->generate($name, $parameters, true);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'routing';
    }

}
