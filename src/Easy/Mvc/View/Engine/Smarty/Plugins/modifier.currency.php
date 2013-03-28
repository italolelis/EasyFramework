<?php

use Easy\Localization\I18n;
use Easy\Utility\Numeric\Number;

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
 * @param string $currency the currency to localize the value
 * @return string|null
 */
function smarty_modifier_currency($value, $currency = null)
{
    if ($currency === null) {
        $lang = I18n::loadLanguage();
        $catalog = I18n::getInstance()->l10n->catalog($lang);
        if (isset($catalog["currency"])) {
            $currency = $catalog["currency"];
        } else {
            $currency = "R$";
        }
    }
    return Number::currency($value, $currency);
}

?>