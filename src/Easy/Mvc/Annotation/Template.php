<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Template
{

    /**
     * The template reference.
     *
     * @var string
     */
    public $template;

    /**
     * The layout to use.
     *
     * @var mixed
     */
    public $layout = null;

    /**
     * The template engine used when a specific template isnt specified.
     *
     * @var string
     */
    public $engine = 'twig';

    /**
     * The associative array of template variables.
     *
     * @var array
     */
    public $vars = array();

    /**
     * Should the template be streamed?
     *
     * @var Boolean
     */
    public $streamable = false;

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Returns the array of templates variables.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param Boolean $streamable
     */
    public function setIsStreamable($streamable)
    {
        $this->streamable = $streamable;
    }

    /**
     * @return Boolean
     */
    public function isStreamable()
    {
        return (Boolean)$this->streamable;
    }

    /**
     * Sets the template variables
     *
     * @param array $vars The template variables
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    }

    /**
     * Returns the engine used when guessing template names
     *
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Sets the engine used when guessing template names
     *
     * @param string
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * Sets the template logic name.
     *
     * @param string $template The template logic name
     */
    public function setValue($template)
    {
        $this->setTemplate($template);
    }

    /**
     * Returns the template reference.
     *
     * @return TemplateReference
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the template reference.
     *
     * @param TemplateReference|string $template The template reference
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Returns the annotation alias name.
     *
     * @return string
     * @see ConfigurationInterface
     */
    public function getAliasName()
    {
        return 'template';
    }

    /**
     * Only one template directive is allowed
     *
     * @return Boolean
     * @see ConfigurationInterface
     */
    public function allowArray()
    {
        return false;
    }

}