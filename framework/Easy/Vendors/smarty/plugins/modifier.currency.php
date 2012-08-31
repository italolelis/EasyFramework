<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {currency} modifier plugin
 *
 * Type:     function<br>
 * Name:     currency<br>
 * Purpose:  print out a localized currency value
 *
 * @author Ítalo Lelis <italolelis@gmail.com>
 * @param array $value the value to convert
 * @return string|null
 */
function smarty_modifier_currency($value, $currency = null)
{
    if ($currency === null) {
        $lang = \Easy\Localization\I18n::getInstance()->l10n->get();
        $catalog = \Easy\Localization\I18n::getInstance()->l10n->catalog($lang);
        $currency = $catalog["currency"];
    }

    return Easy\Utility\Numeric\Number::currency($value, $currency);
}

?>