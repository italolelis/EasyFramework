<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SmartyBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * SmartyExtension.
 *
 * This is the class that loads and manages SmartyBundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class SmartyExtension extends Extension
{

    /**
     * Responds to the smarty configuration parameter.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('smarty.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $engineDefinition = $container->getDefinition('templating.engine.smarty');

        if (!empty($config['globals'])) {
            foreach ($config['globals'] as $key => $global) {
                if (isset($global['type']) && 'service' === $global['type']) {
                    $engineDefinition->addMethodCall('addGlobal', array($key, new Reference($global['id'])));
                } else {
                    $engineDefinition->addMethodCall('addGlobal', array($key, $global['value']));
                }
            }
        }
        $container->setParameter('smarty.options', $config['options']);

        /**
         * @note Caching of Smarty classes was causing issues because of the
         * include_once directives used in Smarty.class.php so this
         * feature is disabled.
         *
         * <code>
        $this->addClassesToCompile(array(
        'Smarty',
        'Smarty_Internal_Data',
        'Smarty_Internal_Templatebase',
        'Smarty_Internal_Template',
        'Smarty_Resource',
        'Smarty_Internal_Resource_File',
        'Smarty_Cacheresource',
        'Smarty_Internal_Cacheresource_File',
        ));
         * </code>
         */
    }

    public function getAlias()
    {
        return 'smarty';
    }

}