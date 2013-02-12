<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Core;

/**
 * Configuration class. Used for managing runtime configuration information.
 *
 * Provides features for reading and writing to the runtime configuration, as well
 * as methods for loading additional configuration files or storing runtime configuration
 * for future use.
 *
 * @package Easy.Core
 */
class Config extends Object
{

    /**
     * Array of values currently stored in Configure.
     *
     * @var array
     */
    protected static $_values = array(
        'debug' => false
    );

    /**
     * Used to store a dynamic variable in Configure.
     *
     * Usage:
     * {{{
     * Configure::write('One.key1', 'value of the Configure::One[key1]');
     * Configure::write(array('One.key1' => 'value of the Configure::One[key1]'));
     * Configure::write('One', array(
     *     'key1' => 'value of the Configure::One[key1]',
     *     'key2' => 'value of the Configure::One[key2]'
     * );
     *
     * Configure::write(array(
     *     'One.key1' => 'value of the Configure::One[key1]',
     *     'One.key2' => 'value of the Configure::One[key2]'
     * ));
     * }}}
     *
     * @link http://book.cakephp.org/2.0/en/de
     * velopment/configuration.html#Configure::write
     * @param array $config Name of var to write
     * @param mixed $value Value to set for var
     * @return boolean True if write was successful
     */
    public static function write($config, $value = null)
    {
        if (!is_array($config)) {
            $config = array($config => $value);
        }

        foreach ($config as $name => $value) {
            $pointer = &static::$_values;
            foreach (explode('.', $name) as $key) {
                $pointer = &$pointer[$key];
            }
            $pointer = $value;
            unset($pointer);
        }
        return true;
    }

    /**
     * Used to read information stored in Configure.  Its not
     * possible to store `null` values in Configure.
     *
     * Usage:
     * {{{
     * Configure::read('Name'); will return all values for Name
     * Configure::read('Name.key'); will return only the value of Configure::Name[key]
     * }}}
     *
     * @linkhttp://book.cakephp.org/2.0/en/development/configuration.html#Configure::read
     * @param string $var Variable to obtain.  Use '.' to access array elements.
     * @return mixed value stored in configure, or null.
     */
    public static function read($var = null)
    {
        if ($var === null) {
            return static::$_values;
        }
        if (isset(static::$_values[$var])) {
            return static::$_values[$var];
        }
        $pointer = &static::$_values;
        foreach (explode('.', $var) as $key) {
            if (isset($pointer[$key])) {
                $pointer = &$pointer[$key];
            } else {
                return null;
            }
        }
        return $pointer;
    }

    /**
     * Used to delete a variable from Configure.
     *
     * Usage:
     * {{{
     * Configure::delete('Name'); will delete the entire Configure::Name
     * Configure::delete('Name.key'); will delete only the Configure::Name[key]
     * }}}
     *
     * @link http://book.cakephp.org/2.0/en/development/configuration.html#Configure::delete
     * @param string $var the var to be deleted
     * @return void
     */
    public static function delete($var = null)
    {
        $keys = explode('.', $var);
        $last = array_pop($keys);
        $pointer = &static::$_values;
        foreach ($keys as $key) {
            $pointer = &$pointer[$key];
        }
        unset($pointer[$last]);
    }

}