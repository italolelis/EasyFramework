<?php

namespace Easy\Model;

use Easy\Core\Object,
    Easy\Core\App,
    Easy\Core\Config,
    Easy\Utility\Inflector,
    Easy\Error;

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

    protected static function _init()
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
    public static function getDataSource($dbConfig = null)
    {
        if (!static::$init) {
            static::_init();
        }

        if (!empty(static::$datasources[$dbConfig])) {
            $return = static::$datasources[$dbConfig];
            return $return;
        }

        $environment = App::getEnvironment();
        $class = static::loadDatasource($environment, $dbConfig);
        return static::$datasources[$dbConfig] = new $class(static::$config[$environment][$dbConfig]);
    }

    /**
     *  Carrega um datasource.
     *
     *  @param string $datasource Nome do datasource
     *  @return boolean Verdadeiro se o datasource existir e for carregado
     */
    public static function loadDatasource($environment, $dbConfig)
    {
        if (isset(static::$config[$environment][$dbConfig])) {
            $config = static::$config[$environment][$dbConfig];
        } else {
            throw new Error\MissingConnectionException(array(
                "config" => $dbConfig
            ));
        }

        $class = Inflector::camelize($config['driver']);
        $class = App::classname($class, 'Model/Datasources', 'Datasource');

        if (!class_exists($class)) {
            throw new Error\MissingDatasourceException(array(
                'class' => $class
            ));
        }
        return $class;
    }

}