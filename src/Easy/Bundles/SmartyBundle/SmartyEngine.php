<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SmartyBundle;

use Easy\Bundles\SmartyBundle\Extension\ExtensionInterface;
use Easy\Bundles\SmartyBundle\Extension\Filter\FilterInterface;
use Easy\Bundles\SmartyBundle\Extension\Plugin\PluginInterface;
use Easy\HttpKernel\KernelInterface;
use Easy\Mvc\View\Engine\Engine;
use Easy\Mvc\View\TemplateNameParserInterface;
use ReflectionClass;
use Smarty;
use SmartyException;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class handles the smarty engine
 * @since 2.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class SmartyEngine extends Engine
{

    /**
     * @var Smarty Smarty Object
     */
    protected $smarty;
    protected $parser;
    protected $plugins;
    protected $extensions;
    protected $filters;
    protected $globals;
    protected $logger;

    /**
     * Initializes a new instance of the SmartyEngine class.
     * @param array $options The options
     */
    public function __construct(Smarty $smarty, TemplateNameParserInterface $parser, KernelInterface $kernel, $options = array(), $logger = null)
    {
        $this->parser = $parser;
        $this->smarty = $smarty;
        $this->logger = $logger;
        $this->globals = array();
        Smarty::muteExpectedErrors();

        // There are no default extensions.
        $this->extensions = array();

        foreach (array('autoload_filters') as $property) {
            if (isset($options[$property])) {
                $this->smarty->$property = $options[$property];
                unset($options[$property]);
            }
        }
        /**
         * @warning If you added template dirs to the Smarty instance prior to
         * the loading of this engine these WILL BE LOST because the setter
         * method setTemplateDir() is used below. Please use the following
         * method instead:
         *   $container->get('templating.engine.smarty')->addTemplateDir(
         *   '/path/to/template_dir');
         */
        foreach ($options as $property => $value) {
            $this->smarty->{$this->smartyPropertyToSetter($property)}($value);
        }

        $container = $kernel->getContainer();
        /**
         * Define a set of template dirs to look for. This will allow the
         * usage of the following syntax:
         * <code>file:[WebkitBundle]/Default/layout.html.tpl</code>
         *
         * See {@link http://www.smarty.net/docs/en/resources.tpl} for details
         */
        $appRoot = dirname($container->getParameter('kernel.application_dir'));

        $bundlesTemplateDir = array(
            $appRoot . "/src",
            $appRoot . "/app/Resources/views"
        );

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $name = explode('\\', $bundle);
            $name = end($name);
            $reflection = new ReflectionClass($bundle);
            if (is_dir($dir = dirname($reflection->getFilename()) . '/Resources/views')) {
                $bundlesTemplateDir[$name] = $dir;
            }
        }
        $this->smarty->addTemplateDir($bundlesTemplateDir);

        parent::__construct($kernel);
    }

    /**
     * Registers an extension.
     *
     * @param ExtensionInterface $extension An ExtensionInterface instance
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[$extension->getName()] = $extension;
    }

    /**
     * Removes an extension by name.
     *
     * @param string $name The extension name
     */
    public function removeExtension($name)
    {
        unset($this->extensions[$name]);
    }

    /**
     * Registers an array of extensions.
     *
     * @param array $extensions An array of extensions
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = array();

        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * Returns all registered extensions.
     *
     * @return array An array of extensions
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Adds a filter to the collection.
     *
     * @param mixed $filter A FilterInterface instance
     */
    public function addFilter(FilterInterface $filter)
    {
        if (null === $this->filters) {
            $this->getFilters();
        }

        $this->filters[] = $filter;
    }

    /**
     * Gets the collection of filters.
     *
     * @return array An array of Filters
     */
    public function getFilters()
    {
        if (null === $this->filters) {
            $this->filters = array();
            foreach ($this->getExtensions() as $extension) {
                $this->filters = array_merge($this->filters, $extension->getFilters());
            }
        }

        return $this->filters;
    }

    /**
     * Dynamically register filters to Smarty.
     */
    public function registerFilters()
    {
        foreach ($this->getFilters() as $filter) {
            try {
                $this->smarty->registerFilter($filter->getType(), $filter->getCallback());
            } catch (SmartyException $e) {
                if (null !== $this->logger) {
                    $this->logger->warn(sprintf("SmartyException caught: %s.", $e->getMessage()));
                }
            }
        }
    }

    /**
     * Adds a plugin to the collection.
     *
     * @param mixed $plugin A PluginInterface instance
     */
    public function addPlugin(PluginInterface $plugin)
    {
        if (null === $this->plugins) {
            $this->getPlugins();
        }

        $this->plugins[] = $plugin;
    }

    /**
     * Gets the collection of plugins, optionally filtered by an extension
     * name.
     *
     * @return array An array of plugins
     */
    public function getPlugins($extensionName = false)
    {
        if (null === $this->plugins) {
            $this->plugins = array();
            foreach ($this->getExtensions() as $extension) {
                $this->plugins = array_merge($this->plugins, $extension->getPlugins());
            }
        }

        // filter plugins that belong to $extension
        if ($extensionName) {

            $plugins = array();
            foreach (array_keys($this->plugins) as $k) {
                if ($extensionName == $this->plugins[$k]->getExtension()->getName()) {
                    $plugins[] = $this->plugins[$k];
                }
            }

            return $plugins;
        }

        return $this->plugins;
    }

    /**
     * Dynamically register plugins to Smarty.
     */
    public function registerPlugins()
    {
        foreach ($this->getPlugins() as $plugin) {
            try {
                $this->smarty->registerPlugin($plugin->getType(), $plugin->getName(), $plugin->getCallback());
            } catch (SmartyException $e) {
                if (null !== $this->logger) {
                    $this->logger->debug(sprintf("SmartyException caught: %s.", $e->getMessage()));
                }
            }
        }
    }

    /**
     * Registers a Global.
     *
     * @param string $name  The global name
     * @param mixed $value The global value
     */
    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    /**
     * Gets the registered Globals.
     *
     * @return array An array of Globals
     */
    public function getGlobals($load_extensions = true)
    {
        if (true === $load_extensions) {
            foreach ($this->getExtensions() as $extension) {
                $this->globals = array_merge($this->globals, $extension->getGlobals());
            }
        }

        return $this->globals;
    }

    /**
     * Pass methods not available in this engine to the Smarty instance.
     */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->smarty, $name), $args);
    }

    /**
     * Returns the Smarty instance.
     *
     * @return Smarty The Smarty instance
     */
    public function getSmarty()
    {
        $this->registerFilters();
        $this->registerPlugins();
        $this->smarty->assign($this->getGlobals());

        return $this->smarty;
    }

    /**
     * {@inherited}
     */
    public function render($name, array $parameters = array())
    {
        $this->registerFilters();
        $this->registerPlugins();

        // attach the global variables
        $parameters = array_replace($this->getGlobals(), $this->getHelpers(), $parameters);
        $this->smarty->assign($parameters);

        $template = $this->parser->parse($name);

        $layout = $this->getLayout();
        if (strstr($layout, ":")) {
            $bundle = $this->getBundlePath(strstr($layout, ":", true));
            $layout_name = str_replace(":", "", strstr($layout, ":"));
            $layout = $bundle . 'Resources/layouts/' . $layout_name;
        }

        $path = $this->getViewPath($template);

        if (!empty($layout)) {
            $content = $this->smarty->fetch("extends:{$layout}.tpl|{$path}");
        } else {
            $content = $this->smarty->fetch("file:{$path}");
        }

        return $content;
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
     * Returns true if this class is able to render the given template.
     *
     * @param string $name A template name
     *
     * @return Boolean True if this class supports the given resource, false otherwise
     */
    public function supports($name)
    {
        if ($name instanceof \Smarty_Internal_Template) {
            return true;
        }

        $template = $this->parser->parse($name);

        // Keep 'tpl' for backwards compatibility.
        return in_array($template->get('engine'), array('smarty', 'tpl'));
    }

    public function getExtension()
    {
        return 'tpl';
    }

    public function exists($name)
    {

    }

    /**
     * Get the setter method for a Smarty class variable (property).
     */
    protected function smartyPropertyToSetter($property)
    {
        $words = explode('_', strtolower($property));

        $setter = 'set';
        foreach ($words as $word) {
            $setter .= ucfirst(trim($word));
        }

        return $setter;
    }

}
