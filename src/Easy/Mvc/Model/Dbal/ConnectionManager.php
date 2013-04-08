<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\Dbal;

use Easy\Core\Object;
use Easy\Mvc\Model\Dbal\Exceptions\MissingConnectionException;

/**
 * The EntityManager is the central access point to ORM functionality.
 * 
 * @since 1.5
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
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

    protected static function init($configs)
    {
        static::$config = $configs;
        static::$init = true;
    }

    /**
     * Gets the list of available DataSource connections
     * This will only return the datasources instantiated by this manager
     *
     * @return array List of available connections
     * @throws MissingConnectionException, MissingDataSourceException
     */
    public static function getDriver($configs, $dbConfig = null)
    {
        if (isset($configs["datasource"])) {
            $configs = $configs["datasource"];
        }
        if (!static::$init) {
            static::init($configs);
        }
        if (!empty(static::$datasources[$dbConfig])) {
            return static::$datasources[$dbConfig];
        }

        if (isset(static::$config[$dbConfig])) {
            $config = static::$config[$dbConfig];
        } else {
            throw new MissingConnectionException(__('Database connection "%s" is missing, or could not be created.', $dbConfig));
        }

        $class = new $config['driver']($config);
        return static::$datasources[$dbConfig] = $class;
    }

}