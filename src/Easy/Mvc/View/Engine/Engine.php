<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Engine;

use Easy\HttpKernel\KernelInterface;
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\View\Engine\EngineInterface;
use Easy\Mvc\View\TemplateReferenceInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @since 0.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
abstract class Engine implements EngineInterface
{

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var KernelInterface
     */
    protected $kernel;
    protected $layout = 'Layout';

    /**
     * Initializes a new instance of the EngineInterface.
     * @param Controller $controller The controller to be associated with the view
     * @param array $options The options
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->container = $this->kernel->getContainer();
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout()
    {
        return $this->layout;
    }

    protected function getBundlePath($bundleName)
    {
        $bundle = $this->kernel->getBundle($bundleName);
        $namespace = $bundle->getNamespace();
        $bundlePath = strstr($namespace, "\\", true);
        if ($bundlePath) {
            return $bundlePath;
        } else {
            return $namespace;
        }
    }

    protected function getViewPath(TemplateReferenceInterface $template)
    {
        $viewPath = $template->getPath();
        $bundlePath = $this->getBundlePath($template->get('bundle'));
        return str_replace("@", "", $bundlePath . "/" . str_replace($bundlePath, "", $viewPath));
    }

    protected function checkDir($dir)
    {
        $fs = new Filesystem();
        $fs->mkdir($dir);
    }

    protected function getHelpers()
    {
        $helpersTags = $this->container->findTaggedServiceIds('templating.helper');
        $helpers = array();
        foreach ($helpersTags as $id => $definition) {
            $service = $this->container->get($id);
            $helpers[ucfirst(str_replace("helper.", "", $id))] = $service;
        }
        return $helpers;
    }

}