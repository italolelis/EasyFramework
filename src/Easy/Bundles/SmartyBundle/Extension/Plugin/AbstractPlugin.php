<?php

namespace Easy\Bundles\SmartyBundle\Extension\Plugin;

use Easy\Bundles\SmartyBundle\Extension\ExtensionInterface;

/**
 * The Plugin base class represents a OO approach to the Smarty plugin
 * architecture.
 *
 * See {@link http://www.smarty.net/docs/en/plugins.tpl}.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
abstract class AbstractPlugin implements PluginInterface
{

    /**
     * Available plugin types.
     * @var array
     */
    protected static $types = array('function', 'modifier', 'block',
        'compiler', 'prefilter', 'postfilter', 'outputfilter', 'resource',
        'insert');
    protected $name;
    protected $extension;
    protected $method;

    /**
     * Constructor.
     *
     * @param string $name      The plugin name
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
     * Get the plugin name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the plugin name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Return the plugin callback.
     */
    public function getCallback()
    {
        return array($this->extension, $this->method);
    }

    /**
     * Return the Extension.
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Check if type is in the supported list.
     */
    public function validateType()
    {
        if (!in_array($this->getType(), static::$types)) {
            throw new \RuntimeException("Plugin type: '" . $this->getType() . "' is not allowed.");
        }
    }

}
