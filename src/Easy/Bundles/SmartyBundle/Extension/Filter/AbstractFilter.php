<?php

namespace Easy\Bundles\SmartyBundle\Extension\Filter;

use Easy\Bundles\SmartyBundle\Extension\ExtensionInterface;

/**
 * The Plugin base class represents a OO approach to the Smarty plugin
 * architecture.
 *
 * See {@link http://www.smarty.net/docs/en/api.register.filter.tpl}.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
abstract class AbstractFilter implements FilterInterface
{

    /**
     * Available filter types.
     * @var array
     */
    protected static $types = array('pre', 'post', 'output', 'variable');
    protected $name;
    protected $extension;
    protected $method;

    /**
     * Constructor.
     *
     * @param string $name      The filter name
     * @param ExtensionInterface $extension A ExtensionInterface instance
     * @param string $method    Method name
     */
    public function __construct($name, ExtensionInterface $extension, $method)
    {
        $this->name = $name;
        $this->extension = $extension;
        $this->method = $method;
    }

    /**
     * Return the filter callback.
     */
    public function getCallback()
    {
        return array($this->extension, $this->method);
    }

    public function validateType()
    {
        if (!in_array($this->getType(), static::$types)) {
            throw new \RuntimeException("Filter type: '" . $this->getType() . "' is not allowed.");
        }
    }

}
