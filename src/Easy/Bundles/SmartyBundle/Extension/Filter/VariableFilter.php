<?php

namespace Easy\Bundles\SmartyBundle\Extension\Filter;

/**
 * See {@link http://www.smarty.net/docs/en/api.register.filter.tpl}.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class VariableFilter extends AbstractFilter
{

    public function getType()
    {
        return 'variable';
    }

}
