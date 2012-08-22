<?php

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
    private $config = array();

    /**
     * Holds instances DataSource objects
     *
     * @var array
     */
    private $datasources = array();
    protected static $instance;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new ConnectionManager();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->config = Config::read("datasource");
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
        $self = self::instance();
        if (!empty($self->config)) {
            $environment = App::getEnvironment();

            if (isset($self->config[$environment][$dbConfig])) {
                $config = $self->config[$environment][$dbConfig];
            } else {
                throw new MissingConnectionException(array(
                    $dbConfig
                ));
            }

            $class = Inflector::camelize($config['driver'] . "Datasource");

            if (isset($self->datasources[$dbConfig])) {
                return $self->datasources[$dbConfig];
            } elseif (self::loadDatasource($class)) {
                $self->datasources[$dbConfig] = new $class($config);
                return $self->datasources[$dbConfig];
            } else {
                throw new MissingDataSourceException(array(
                    $class
                ));
            }
        }
    }

    /**
     *  Carrega um datasource.
     *
     *  @param string $datasource Nome do datasource
     *  @return boolean Verdadeiro se o datasource existir e for carregado
     */
    public static function loadDatasource($datasource = null)
    {
        if (!class_exists($datasource)) {
            if (App::path("Datasource", $datasource)) {
                App::uses($datasource, "Datasource");
            }
        }
        return class_exists($datasource);
    }

}