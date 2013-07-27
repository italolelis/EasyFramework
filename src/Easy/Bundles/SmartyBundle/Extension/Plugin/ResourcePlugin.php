<?php

namespace Easy\Bundles\SmartyBundle\Extension\Plugin;

/**
 * See {@link http://www.smarty.net/docs/en/plugins.functions.tpl}.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class ResourcePlugin extends Plugin
{

    public function getType()
    {
        return 'resource';
    }

}
