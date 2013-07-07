<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View;

/**
 * Internal representation of a template.
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class TemplateReference implements TemplateReferenceInterface
{

    protected $parameters;

    public function __construct($bundle = null, $controller = null, $name = null, $engine = null)
    {
        $this->parameters = array(
            'bundle' => $bundle,
            'controller' => $controller,
            'name' => $name,
            'engine' => $engine,
        );
    }

    public function __toString()
    {
        return $this->getLogicalName();
    }

    /**
     * Sets a template parameter.
     *
     * @param string $name  The parameter name
     * @param string $value The parameter value
     *
     * @return TemplateReferenceInterface The TemplateReferenceInterface instance
     *
     * @throws  \InvalidArgumentException if the parameter is not defined
     *
     * @api
     */
    public function set($name, $value)
    {
        if (array_key_exists($name, $this->parameters)) {
            $this->parameters[$name] = $value;
        } else {
            throw new \InvalidArgumentException(sprintf('The template does not support the "%s" parameter.', $name));
        }

        return $this;
    }

    /**
     * Gets a template parameter.
     *
     * @param string $name The parameter name
     *
     * @return string The parameter value
     *
     * @throws  \InvalidArgumentException if the parameter is not defined
     *
     * @api
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        throw new \InvalidArgumentException(sprintf('The template does not support the "%s" parameter.', $name));
    }

    /**
     * Gets the template parameters.
     *
     * @return array An array of parameters
     *
     * @api
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Returns the path to the template
     *  - as a path when the template is not part of a bundle
     *  - as a resource when the template is part of a bundle
     *
     * @return string A path to the template or a resource
     */
    public function getPath()
    {
        $controller = str_replace('\\', '/', $this->get('controller'));

        $path = (empty($controller) ? '' : $controller . '/') . $this->get('name') . '.' . $this->get('engine');

        return empty($this->parameters['bundle']) ? '/Resources/views/' . $path : '@' . $this->get('bundle') . '/Resources/views/' . $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName()
    {
        return sprintf('%s:%s:%s.%s', $this->parameters['bundle'], $this->parameters['controller'], $this->parameters['name'], $this->parameters['engine']);
    }

}
