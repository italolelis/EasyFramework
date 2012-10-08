<?php

namespace Easy\Model;

use Easy\Core\App;
use Easy\Core\Config;
use Easy\Core\Object;
use Easy\Error;
use Easy\Utility\Inflector;

/**
 *  Connection é a classe que cuida das conexões com banco de dados no EasyFramework,
 *  encontrando e carregando datasources de acordo com a configuração desejada.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
class ConnectionManager extends Object
{

    /**
     * Holds a loaded instance of the Connections object
     *
     * @var DATABASE_CONFIG
     */
    private static $config = array();
    private static $init = false;

    /**
     * Holds instances DataSource objects
     *
     * @var array
     */
    private static $datasources = array();

    protected static function init()
    {
        static::$config = Config::read("datasource");
        static::$init = true;
    }

    /**
     * Gets the list of available DataSource connections
     * This will only return the datasources instantiated by this manager
     *
     * @return array List of available connections
     * @throws MissingConnectionException, MissingDataSourceException
     */
    public static function getDriver($dbConfig = null)
    {
        $environment = App::getEnvironment();

        if (!static::$init) {
            static::init();
        }

        if (!empty(static::$datasources[$dbConfig])) {
            return static::$datasources[$dbConfig];
        }

        if (isset(static::$config[$environment][$dbConfig])) {
            $config = static::$config[$environment][$dbConfig];
        } else {
            throw new Error\MissingConnectionException(array(
                "config" => $dbConfig
            ));
        }

        $factory = new DriverFactory($config);
        $class = $factory->build($config['driver']);
        return static::$datasources[$dbConfig] = $class;
    }

}