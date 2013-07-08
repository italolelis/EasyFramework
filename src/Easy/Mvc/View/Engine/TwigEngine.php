<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Engine;

use Easy\Collections\Dictionary;
use Easy\Mvc\Routing\Mapper;
use Easy\Utility\Hash;
use Twig_Environment;
use Twig_Loader_String;

/**
 * This class handles the twig engine 
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class TwigEngine extends Engine
{

    /**
     * @var Twig_Environment The Twig object
     */
    protected $twig;
    protected $options = array(
        'debug' => false,
        'cache' => false,
        'strict_variables' => false,
        'optimizations' => -1
    );
    protected $viewVars;

    /**
     * Initializes a new instance of the TwigEngine class.
     * @param array $options The smarty options
     */
    public function __construct(\Easy\Mvc\Controller\Controller $controller, $options = array())
    {
        parent::__construct($controller, $options);
        $this->viewVars = new Dictionary();

        $appDir = $controller->getKernel()->getApplicationRootDir();

        $prefixes = Mapper::getPrefixes();
        foreach ($prefixes as $prefix) {
            $this->options["template_dir"][] = $appDir . "/Areas/" . $prefix . "/View/Pages";
            $this->options["template_dir"][] = $appDir . "/Areas/" . $prefix . "View/Layouts";
            $this->options["template_dir"][] = $appDir . "/Areas/" . $prefix . "View/Elements";
        }

        $this->options = Hash::marge($this->options, $options);
        $loader = new Twig_Loader_String($this->options['template_dir']);
        $this->twig = new Twig_Environment($loader, $this->options);
    }

    /**
     * @inherited
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @inherited
     */
    public function display($layout, $view, $output = true)
    {
        list(, $view) = namespaceSplit($view);
        $ext = "html.twig";
        $method = $output ? 'display' : 'render';


        if (!empty($layout)) {
            $twigLayout = $this->twig->loadTemplate($layout . "." . $ext);
            $this->viewVars->add('layout', $twigLayout);
            return $this->twig->{$method}($view . "." . $ext, $this->viewVars->getArray());
        } else {
            $twigView = $this->twig->loadTemplate($layout . "." . $ext);
            return $twigView->{$method}($view . $ext, $this->viewVars->getArray());
        }
    }

    /**
     * @inherited
     */
    public function set($var, $value)
    {
        $this->viewVars->add($var, $value);
    }

    public function render($name, $layout, $output = true)
    {
        
    }

    public function renderResponse($name, $layout)
    {
        
    }

}
