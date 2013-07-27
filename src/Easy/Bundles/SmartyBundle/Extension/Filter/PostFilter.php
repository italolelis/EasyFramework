<?php

namespace Easy\Bundles\SmartyBundle\Extension\Filter;

/**
 * Postfilters are used to process the compiled output of the template (the PHP
 * code) immediately after the compilation is done but before the compiled
 * template is saved to the filesystem. The first parameter to the postfilter
 * `function is the compiled template code, possibly modified by other
 * postfilters. The plugin is supposed to return the modified version of this
 * code.
 *
 * See {@link http://www.smarty.net/docs/en/plugins.prefilters.postfilters.tpl}.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class PostFilter extends AbstractFilter
{

    public function getType()
    {
        return 'post';
    }

}
