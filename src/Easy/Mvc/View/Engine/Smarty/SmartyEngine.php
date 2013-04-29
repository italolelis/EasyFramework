<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Engine\Smarty;

use Easy\HttpKernel\KernelInterface;
use Easy\Mvc\Controller\Metadata\ControllerMetadata;
use Easy\Mvc\View\Engine\Engine;
use Easy\Mvc\View\TemplateNameParserInterface;
use Easy\Utility\Hash;
use Smarty;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class handles the smarty engine 
 * @since 0.1
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class SmartyEngine extends Engine
{

    /**
     * @var Smarty Smarty Object
     */
    protected $smarty;
    protected $parser;

    /**
     * Initializes a new instance of the SmartyEngine class.
     *      * @param Controller $controller The controller to be associated with the view
     * @param array $options The options
     */
    public function __construct(TemplateNameParserInterface $parser, KernelInterface $kernel, ControllerMetadata $metadata, $options = array())
    {
        $this->parser = $parser;
        $this->smarty = new Smarty();
        Smarty::muteExpectedErrors();
        parent::__construct($kernel, $metadata, $options);
        //Build the template directory
        $this->loadOptions();
    }

    /**
     * @inherited
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @inherited
     */
    public function render($name, $layout, $output = true)
    {
        $view = $this->parser->parse($name);

        if ($layout === null) {
            $layout = $this->getLayout();
        }

        if (!empty($layout)) {
            $content = $this->smarty->fetch("extends:{$layout}.tpl|{$view->getPath()}", null, null, null, $output);
        } else {
            $content = $this->smarty->fetch("file:{$view->getPath()}", null, null, null, $output);
        }

        return $content;
    }

    /**
     * @inherited
     */
    public function renderResponse($name, $layout)
    {
        $content = $this->render($name, $layout, false);

        $response = new Response();
        $response->setContent($content);
        return $response;
    }

    /**
     * @inherited
     */
    public function set($var, $value)
    {
        return $this->smarty->assign($var, $value);
    }

    private function loadOptions()
    {
        $tmpFolder = $this->kernel->getTempDir();
        $cacheDir = $this->kernel->getCacheDir();
        $appDir = $this->kernel->getContainer()->get('bundle_guesser')->getBundle()->getPath();
        $rootDir = $this->kernel->getFrameworkDir();
        $appRoot = dirname($this->kernel->getApplicationRootDir());

        $defaults = array(
            "template_dir" => array(
                'views' => $appRoot . '/src',
                'layouts' => $appDir . "/View/Layouts",
                'elements' => $appDir . "/View/Elements"
            ),
            "compile_dir" => $tmpFolder . "/views/",
            "cache_dir" => $cacheDir . "/views/",
            "plugins_dir" => array(
                $rootDir . "/Mvc/View/Engine/Smarty/Plugins"
            ),
            "cache" => false
        );

        $this->options = Hash::merge($defaults, $this->options);

        $this->smarty->addTemplateDir($this->options["template_dir"]);
        $this->smarty->addPluginsDir($this->options["plugins_dir"]);

        $this->checkDir($this->options["compile_dir"]);
        $this->smarty->setCompileDir($this->options["compile_dir"]);

        $this->checkDir($this->options["cache_dir"]);
        $this->smarty->setCacheDir($this->options["cache_dir"]);

        if ($this->options['cache']) {
            $this->smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $this->smarty->setCacheLifetime($this->options['cache']['lifetime']);
        }
    }

    private function checkDir($dir)
    {
        $fs = new Filesystem();
        $fs->mkdir($dir);
    }

}
