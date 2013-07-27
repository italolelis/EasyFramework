<?php


namespace Easy\Bundles\SmartyBundle\Extension;

interface ExtensionInterface
{
    /**
     * Returns a list of Plugins to add to the existing list.
     *
     * @return array An array of Plugins
     *
     * @since  0.1.0
     * @author Vítor Brandão <vitor@noiselabs.org>
     */
    public function getPlugins();

    /**
     * Returns a list of Filters to add to the existing list.
     *
     * @return array An array of Filters
     *
     * @since  0.1.0
     * @author Vítor Brandão <vitor@noiselabs.org>
     */
    public function getFilters();

    /**
     * Returns a list of Globals to add to the existing list.
     *
     * @return array An array of Globals
     *
     * @since  0.1.0
     * @author Vítor Brandão <vitor@noiselabs.org>
     */
    public function getGlobals();

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName();
}
