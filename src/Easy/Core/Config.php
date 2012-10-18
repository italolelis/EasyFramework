<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 0.2
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Core;

use Easy\Cache\Cache;
use Easy\Routing\Mapper;
use Easy\Utility\Hash;
use Easy\Configure\IConfigReader;
use Easy\Configure\ConfigureFactory;
use Easy\Error\Error;

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
     * Configured reader classes, used to load config files from resources
     *
     * @var array
     * @see Configure::load()
     */
    protected static $_readers = array();

    /**
     * Initializes configure and runs the bootstrap process.
     * Bootstrapping includes the following steps:
     *
     * - Setup App array in Configure.
     * - Include app/Config/bootstrap.php.
     * - Load core configurations.
     * - Load cache configurations.
     * - Load routes configurations.
     * - Setup error/exception handlers.
     *
     * @param boolean $boot
     * @return void
     */
    public static function bootstrap($boot = true)
    {
        if ($boot) {
            App::build();
            static::load('bootstrap');
            $engine = Config::read('configEngine');

            static::loadCoreConfig($engine);
            static::loadCacheConfig($engine);
            static::loadRoutesConfig($engine);
            //TODO: Better Implementation
            require CORE . 'Configure' . DS . 'routes.php';
            App::init();

            /* Handle the Exceptions and Errors */
            Error::handleExceptions(Config::read('Exception'));
            Error::handleErrors(Config::read('Error'));
        }
    }

    /**
     * Loads route config file
     * @param string $engine
     */
    private static function loadRoutesConfig($engine)
    {
        static::load('routes', $engine);
        $connects = Config::read('Routing.connect');
        if (!empty($connects)) {
            foreach ($connects as $url => $route) {
                $options = Hash::arrayUnset($route, 'options');
                Mapper::connect($url, $route, $options);
            }
        }

        $mapResources = Config::read('Routing.mapResources');
        if (!empty($mapResources)) {
            foreach ($mapResources as $resource => $options) {
                if (is_array($options)) {
                    foreach ($options as $k => $v) {
                        $resource = $k;
                        $options = $v;
                    }
                } else {
                    $resource = $options;
                    $options = array();
                }
                Mapper::mapResources($resource, $options);
            }
        }

        $parseExtensions = Config::read('Routing.parseExtensions');
        if (!empty($parseExtensions)) {
            Mapper::parseExtensions($parseExtensions);
        }
    }

    /**
     * Loads cache config file
     * @param string $engine
     */
    private static function loadCacheConfig($engine)
    {
        static::load('cache', $engine);
        $options = Config::read('Cache.options');
        foreach ($options as $key => $value) {
            Cache::config($key, $value);
        }
    }

    /**
     * Loads core config file
     * @param string $engine
     */
    private static function loadCoreConfig($engine)
    {
        static::load('application', $engine);
        static::load('database', $engine);

        //Locale Definitions
        $timezone = Config::read('App.timezone');
        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }

        //Log Definitions
        $logScopes = Config::read('Log.scopes');
        if (!empty($logScopes)) {
            foreach ($logScopes as $scope => $options) {
                EasyLog::config($scope, $options);
            }
        }
    }

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

    /**
     * Add a new reader to Configure.  Readers allow you to read configuration
     * files in various formats/storage locations.  CakePHP comes with two built-in readers
     * PhpReader and IniReader.  You can also implement your own reader classes in your application.
     *
     * To add a new reader to Configure:
     *
     * `Configure::config('ini', new IniReader());`
     *
     * @param string $name The name of the reader being configured.  This alias is used later to
     *   read values from a specific reader.
     * @param IConfigReader $reader The reader to append.
     * @return void
     */
    public static function configure($name, IConfigReader $reader)
    {
        static::$_readers[$name] = $reader;
    }

    /**
     * Gets the names of the configured reader objects.
     *
     * @param string $name
     * @return array Array of the configured reader objects.
     */
    public static function configured($name = null)
    {
        if ($name) {
            return isset(static::$_readers[$name]);
        }
        return array_keys(static::$_readers);
    }

    /**
     * Remove a configured reader.  This will unset the reader
     * and make any future attempts to use it cause an Exception.
     *
     * @param string $name Name of the reader to drop.
     * @return boolean Success
     */
    public static function drop($name)
    {
        if (!isset(static::$_readers[$name])) {
            return false;
        }
        unset(static::$_readers[$name]);
        return true;
    }

    /**
     * Loads stored configuration information from a resource.  You can add
     * config file resource readers with `Configure::config()`.
     *
     * Loaded configuration information will be merged with the current
     * runtime configuration. You can load configuration files from plugins
     * by preceding the filename with the plugin name.
     *
     * `Configure::load('Users.user', 'default')`
     *
     * Would load the 'user' config file using the default config reader.  You can load
     * app config files by giving the name of the resource you want loaded.
     *
     * `Configure::load('setup', 'default');`
     *
     * If using `default` config and no reader has been configured for it yet,
     * one will be automatically created using PhpReader
     *
     * @param string $key name of configuration resource to load.
     * @param string $type Name of the configured reader to use to read the resource identified by $key.
     * @param boolean $merge if config files should be merged instead of simply overridden
     * @return mixed false if file not found, void if load successful.
     * @throws ConfigureException Will throw any exceptions the reader raises.
     */
    public static function load($key, $type = 'php', $merge = true)
    {
        if (!isset(static::$_readers[$type])) {
            $factory = new ConfigureFactory();
            static::$_readers[$type] = $factory->build($type);
        }
        $values = static::$_readers[$type]->read($key);

        if (is_array($values)) {
            if ($merge) {
                $keys = array_keys($values);
                foreach ($keys as $key) {
                    if (($c = static::read($key)) && is_array($values[$key]) && is_array($c)) {
                        $values[$key] = Hash::merge($c, $values[$key]);
                    }
                }
            }
            return static::write($values);
        }

        return false;
    }

    /**
     * Used to write runtime configuration into Cache.  Stored runtime configuration can be
     * restored using `Configure::restore()`.  These methods can be used to enable configuration managers
     * frontends, or other GUI type interfaces for configuration.
     *
     * @param string $name The storage name for the saved configuration.
     * @param string $cacheConfig The cache configuration to save into.  Defaults to 'default'
     * @param array $data Either an array of data to store, or leave empty to store all values.
     * @return boolean Success
     */
    public static function store($name, $cacheConfig = 'default', $data = null)
    {
        if ($data === null) {
            $data = static::$_values;
        }
        return Cache::write($name, $data, $cacheConfig);
    }

    /**
     * Restores configuration data stored in the Cache into configure.  Restored
     * values will overwrite existing ones.
     *
     * @param string $name Name of the stored config file to load.
     * @param string $cacheConfig Name of the Cache configuration to read from.
     * @return boolean Success.
     */
    public static function restore($name, $cacheConfig = 'default')
    {
        $values = Cache::read($name, $cacheConfig);
        if ($values) {
            return static::write($values);
        }
        return false;
    }

}