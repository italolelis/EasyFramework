<?php

/**
 *  Connection é a classe que cuida das conexões com banco de dados no EasyFramework,
 *  encontrando e carregando datasources de acordo com a configuração desejada.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
class ConnectionManager {

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

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new ConnectionManager();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->config = Config::read("datasource");
    }

    /**
     * Gets the list of available DataSource connections
     * This will only return the datasources instantiated by this manager
     *
     * @return array List of available connections
     */
    public static function getDataSource($environment = null) {
        $self = self::instance();

        if (!empty($self->config)) {
            $environment = is_null($environment) ? APPLICATION_ENV : $environment;

            if (isset($self->config[$environment])) {
                $config = $self->config[$environment];
            } else {
                trigger_error("Não pode ser encontrado as configurações do banco de dados. Verifique /app/config/database.php", E_USER_ERROR);
                return false;
            }

            $class = Inflector::camelize($config['driver'] . "Datasource");

            if (isset($self->datasources[$environment])) {
                return $self->datasources[$environment];
            } elseif (self::loadDatasource($class)) {
                $self->datasources[$environment] = new $class($config);
                return $self->datasources[$environment];
            } else {
                trigger_error("Não foi possível encontrar {$class} datasource", E_USER_ERROR);
                return false;
            }
        }
    }

    /**
     *  Carrega um datasource.
     *
     *  @param string $datasource Nome do datasource
     *  @return boolean Verdadeiro se o datasource existir e for carregado
     */
    public static function loadDatasource($datasource = null) {
        if (!class_exists($datasource)) {
            if (App::path("Datasource", $datasource)) {
                App::uses($datasource, "Datasource");
            }
        }
        return class_exists($datasource);
    }

}

?>