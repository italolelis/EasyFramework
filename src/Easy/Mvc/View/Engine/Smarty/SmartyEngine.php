<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Engine\Smarty;

use Easy\HttpKernel\KernelInterface;
use Easy\Mvc\Controller\Metadata\ControllerMetadata;
use Easy\Mvc\View\Engine\Engine;
use Easy\Mvc\View\TemplateNameParserInterface;
use Smarty;
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
    public function render($name, $layout, $output = true)
    {
        $template = $this->parser->parse($name);

        if ($layout === null) {
            $layout = $this->getLayout();
        }

        if (strstr($layout, ":")) {
            $bundle = $this->getBundlePath(strstr($layout, ":", true));
            $layout_name = str_replace(":", "", strstr($layout, ":"));
            $layout = $bundle . 'Resources/layouts/' . $layout_name;
        }

        $path = $this->getViewPath($template);

        if (!empty($layout)) {
            $content = $this->smarty->fetch("extends:{$layout}.tpl|{$path}", null, null, null, $output);
        } else {
            $content = $this->smarty->fetch("file:{$path}", null, null, null, $output);
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
        $cacheDir = $this->kernel->getCacheDir();
        $bundleResourceDir = $this->kernel->getContainer()->get('bundle_guesser')->getBundle()->getPath();
        $rootDir = $this->kernel->getFrameworkDir();
        $appRoot = dirname($this->kernel->getApplicationRootDir());

        $defaults = array(
            "template_dir" => array(
                'views' => array(
                    $appRoot . "/src"
                ),
                'layouts' => array(
                    $appRoot . "/app/Resources/layouts",
                    $bundleResourceDir . "/Resources/layouts"
                ),
                'elements' => array(
                    $appRoot . "/app/Resources/elements",
                    $bundleResourceDir . "/Resources/elements"
                )
            ),
            "compile_dir" => $cacheDir . "/compiled/",
            "cache_dir" => $cacheDir . "/views/",
            "plugins_dir" => array(
                $rootDir . "/Mvc/View/Engine/Smarty/Plugins"
            ),
            "cache" => false
        );

        $this->options = array_merge_recursive($defaults, (array) $this->options);

        foreach ($this->options["template_dir"] as $dir) {
            $this->smarty->addTemplateDir($dir);
        }

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

}
