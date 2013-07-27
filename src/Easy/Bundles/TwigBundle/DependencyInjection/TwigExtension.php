<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\TwigBundle\DependencyInjection;

use Easy\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * TwigBundleExtension.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class TwigExtension extends Extension
{

    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loader->load('twig.yml');

        foreach ($configs as &$config) {
            if (isset($config['globals'])) {
                foreach ($config['globals'] as $name => $value) {
                    if (is_array($value) && isset($value['key'])) {
                        $config['globals'][$name] = array(
                            'key' => $name,
                            'value' => $config['globals'][$name]
                        );
                    }
                }
            }
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.filesystem');

        //register default paths
        $appRoot = dirname($container->getParameter('kernel.application_dir'));

        $template_dirs = array(
            $appRoot . "/src",
            $appRoot . "/app/Resources/views"
        );

        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()) . '/Resources/views')) {
                $template_dirs[] = $dir;
            }
        }

        $container->setParameter('twig.dirs', $template_dirs);

        // register user-configured paths
        foreach ($config['paths'] as $path => $namespace) {
            if (!$namespace) {
                $twigFilesystemLoaderDefinition->addMethodCall('addPath', array($path));
            } else {
                $twigFilesystemLoaderDefinition->addMethodCall('addPath', array($path, $namespace));
            }
        }

        // register bundles as Twig namespaces
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            if (is_dir($dir = $container->getParameter('kernel.root_dir') . '/Resources/' . $bundle . '/views')) {
                $this->addTwigPath($twigFilesystemLoaderDefinition, $dir, $bundle);
            }

            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()) . '/Resources/views')) {
                $this->addTwigPath($twigFilesystemLoaderDefinition, $dir, $bundle);
            }
        }

        if (is_dir($dir = $container->getParameter('kernel.root_dir') . '/Resources/views')) {
            $twigFilesystemLoaderDefinition->addMethodCall('addPath', array($dir));
        }

        if (!empty($config['globals'])) {
            $def = $container->getDefinition('twig');
            foreach ($config['globals'] as $key => $global) {
                if (isset($global['type']) && 'service' === $global['type']) {
                    $def->addMethodCall('addGlobal', array($key, new Reference($global['id'])));
                } else {
                    $def->addMethodCall('addGlobal', array($key, $global['value']));
                }
            }
        }

        unset(
        $config['form'], $config['globals'], $config['extensions']
        );
        $container->setParameter('twig.options', $config);

        $this->addClassesToCompile(array(
            'Twig_Environment',
            'Twig_Extension',
            'Twig_Extension_Core',
            'Twig_Extension_Escaper',
            'Twig_Extension_Optimizer',
            'Twig_LoaderInterface',
            'Twig_Markup',
            'Twig_Template',
        ));
    }

    private function addTwigPath($twigFilesystemLoaderDefinition, $dir, $bundle)
    {
        $name = $bundle;
        if ('Bundle' === substr($name, -6)) {
            $name = substr($name, 0, -6);
        }
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', array($dir, $name));
    }

}