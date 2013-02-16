<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc\DependencyInjection;

use RuntimeException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder as TreeBuilder2;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * FrameworkExtension configuration structure.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class Configuration implements ConfigurationInterface
{

    private $debug;

    /**
     * Constructor
     *
     * @param Boolean $debug Whether to use the debug mode
     */
    public function __construct($debug)
    {
        $this->debug = (Boolean) $debug;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder2 The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('framework');

        $rootNode
                ->children()
                ->scalarNode('charset')
                ->defaultNull()
                ->beforeNormalization()
                ->ifTrue(function($v) {
                            return null !== $v;
                        })
                ->then(function($v) {
                            $message = 'The charset setting is deprecated. Just remove it from your configuration file.';

                            if ('UTF-8' !== $v) {
                                $message .= sprintf(' You need to define a getCharset() method in your Application Kernel class that returns "%s".', $v);
                            }

                            throw new RuntimeException($message);
                        })
                ->end()
                ->end()
                ->scalarNode('trust_proxy_headers')->defaultFalse()->end()
                ->scalarNode('secret')->defaultNull()->end()
                ->scalarNode('default_locale')->defaultValue('en')->end()
                ->scalarNode('default_timezone')->defaultValue('America/Recife')->end()
                ->end()
        ;

        return $treeBuilder;
    }

}
