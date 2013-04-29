<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Engine;

use Easy\Core\Config;
use Easy\HttpKernel\KernelInterface;
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\View\Engine\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

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
     * @var Request 
     */
    protected $request;

    /**
     * @var KernelInterface 
     */
    protected $kernel;
    protected $config;
    protected $layout = 'Layout';
    protected $options;

    /**
     * Initializes a new instance of the EngineInterface.
     * @param Controller $controller The controller to be associated with the view
     * @param array $options The options
     */
    public function __construct(KernelInterface $kernel, $options = array())
    {
        $this->kernel = $kernel;
        $this->request = $this->kernel->getRequest();
        $this->container = $this->kernel->getContainer();

        $this->options = $options;
        $this->config = Config::read("View");
        // Build the template language
        $this->buildLayouts();
        // Build the template language
        $this->buildElements();
        // Build the template language
        $this->buildHelpers();
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    private function buildHelpers()
    {
        $helpers = $this->container->findTaggedServiceIds('templating.helper');
        foreach ($helpers as $id => $definition) {
            $service = $this->container->get($id);
            $this->set(ucfirst(str_replace("helper.", "", $id)), $service);
        }
    }

    private function buildLayouts()
    {
        if (isset($this->config["layouts"]) && is_array($this->config["layouts"])) {
            $layouts = $this->config["layouts"];
            foreach ($layouts as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    private function buildElements()
    {
        if (isset($this->config["elements"]) && is_array($this->config["elements"])) {
            $elements = $this->config["elements"];
            foreach ($elements as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

}