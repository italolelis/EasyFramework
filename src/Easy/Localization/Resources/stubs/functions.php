<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.
use Easy\Localization\I18n;

/**
 * Returns a translated string if one is found; Otherwise, the submitted message.
 *
 * @param string $value Text to translate
 * @param mixed $args Array with arguments or multiple arguments in function
 * @return mixed translated string
 */
function __($value, $args = null)
{
    if (!$value) {
        return;
    }
    $translated = I18n::translate($value);
    if ($args === null) {
        return $translated;
    } elseif (!is_array($args)) {
        $args = array_slice(func_get_args(), 1);
    }
    return vsprintf($translated, $args);
}

/**
 * Split the namespace from the classname.
 *
 * Commonly used like `list($namespace, $classname) = namespaceSplit($class);`
 *
 * @param string $class The full class name, ie `Cake\Core\App`
 * @return array Array with 2 indexes. 0 => namespace, 1 => classname
 */
function namespaceSplit($class)
{
    $pos = strrpos($class, '\\');
    if ($pos === false) {
        return array('', $class);
    }
    return array(substr($class, 0, $pos), substr($class, $pos + 1));
}