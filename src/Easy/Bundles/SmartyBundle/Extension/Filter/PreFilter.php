<?php

namespace Easy\Bundles\SmartyBundle\Extension\Filter;

/**
 * Prefilters are used to process the source of the template immediately before
 * compilation. The first parameter to the prefilter function is the template
 * source, possibly modified by some other prefilters. The plugin is supposed
 * to return the modified source. Note that this source is not saved anywhere,
 * it is only used for compilation.
 *
 * See {@link http://www.smarty.net/docs/en/plugins.prefilters.postfilters.tpl}.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class PreFilter extends AbstractFilter
{

    public function getType()
    {
        return 'pre';
    }

}
