<?php

namespace Easy\Bundles\SmartyBundle\Extension\Filter;

/**
 * Output filter plugins operate on a template's output, after the template is
 * loaded and executed, but before the output is displayed.
 *
 * See {@link http://www.smarty.net/docs/en/plugins.outputfilters.tpl}.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class OutputFilter extends AbstractFilter
{

    public function getType()
    {
        return 'output';
    }

}
