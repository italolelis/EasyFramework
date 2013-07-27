<?php

namespace Easy\Bundles\SmartyBundle\Extension\Plugin;

/**
 * Variable modifiers can be applied to variables, custom functions or strings.
 * To apply a modifier, specify the value followed by a | (pipe) and the
 * modifier name. A modifier may accept additional parameters that affect its
 * behavior. These parameters follow the modifier name and are separated by a :
 * (colon). Also, all php-functions can be used as modifiers implicitly (more
 * below) and modifiers can be combined.
 *
 * See {@link http://www.smarty.net/docs/en/language.modifiers.tpl}.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class ModifierPlugin extends AbstractPlugin
{

    public function getType()
    {
        return 'modifier';
    }

}
