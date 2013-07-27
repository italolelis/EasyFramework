<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\TwigBundle;

use Easy\HttpKernel\KernelInterface;
use Easy\Mvc\View\Engine\Engine;
use Easy\Mvc\View\TemplateNameParserInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

/**
 * This class handles the twig engine
 * @since 2.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class TwigEngine extends Engine
{

    /**
     * @var Twig_Environment The Twig object
     */
    protected $twig;
    protected $parser;

    /**
     * Initializes a new instance of the TwigEngine class.
     * @param array $options The smarty options
     */
    public function __construct(Twig_Environment $twig, TemplateNameParserInterface $parser, KernelInterface $kernel)
    {
        $this->parser = $parser;
        $this->twig = $twig;
        parent::__construct($kernel);
    }

    /**
     * {@inherited}
     */
    public function render($name, array $parameters = array())
    {
        $template = $this->parser->parse($name);
        $layout = $this->getLayout();
        $path = $this->getViewPath($template);

        $parameters = array_replace($this->getHelpers(), $parameters);
        if (!empty($layout)) {
            $twigLayout = $this->load($layout . "." . $this->getExtension());
            $parameters['layout'] = $twigLayout;
        }

        return $this->load($path)->render($parameters);
    }

    /**
     * {@inherited}
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($this->render($view, $parameters));

        return $response;
    }

    /**
     * {@inherited}
     */
    public function getExtension()
    {
        return 'twig';
    }

    public function exists($name)
    {
        try {
            $this->load($name);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    public function supports($name)
    {
        if ($name instanceof \Twig_Template) {
            return true;
        }

        $template = $this->parser->parse($name);

        return 'twig' === $template->get('engine');
    }

    public function addGlobal($name, $value)
    {
        $this->twig->addGlobal($name, $value);
    }

    public function getGlobals()
    {
        return $this->twig->getGlobals();
    }

    /**
     * Loads the given template.
     *
     * @param mixed $name A template name or an instance of Twig_Template
     *
     * @return \Twig_TemplateInterface A \Twig_TemplateInterface instance
     *
     * @throws \InvalidArgumentException if the template does not exist
     */
    protected function load($name)
    {
        if ($name instanceof \Twig_Template) {
            return $name;
        }

        try {
            return $this->twig->loadTemplate($name);
        } catch (\Twig_Error_Loader $e) {
            throw new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

}
