<?php

namespace Easy\Bundles\SmartyBundle\Extension;

/**
 * Base Extension class.
 *
 * @since  0.1.0
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
abstract class AbstractExtension implements ExtensionInterface
{

    /**
     * Returns a list of Plugins to add to the existing list.
     *
     * @return array An array of Plugins
     *
     * @since  0.1.0
     * @author Vítor Brandão <vitor@noiselabs.org>
     */
    public function getPlugins()
    {
        return array();
    }

    /**
     * Returns a list of Filters to add to the existing list.
     *
     * @return array An array of Filters
     *
     * @since  0.1.0
     * @author Vítor Brandão <vitor@noiselabs.org>
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * Returns a list of globals to add to the existing list.
     *
     * @return array An array of globals
     *
     * @since  0.1.0
     * @author Vítor Brandão <vitor@noiselabs.org>
     */
    public function getGlobals()
    {
        return array();
    }

}
