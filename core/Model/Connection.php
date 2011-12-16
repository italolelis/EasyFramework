<?php

/**
 *  Connection é a classe que cuida das conexões com banco de dados no EasyFramework,
 *  encontrando e carregando datasources de acordo com a configuração desejada.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
class Connection {

    private $config = array();
    private $datasources = array();
    protected static $instance;

    public static function instance() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    /**
     *  Lendo arquivos de configuração do banco de dados.
     */
    public function __construct() {
        $this->config = Config::read("datasource");
    }

    /**
     *  Cria uma instância de um datasource ou retorna outra instância existente.
     *
     *  @param string $environment Configuração de ambiente a ser usada
     *  @return object Instância do datasource
     */
    public static function get($environment = null) {
        $self = self::instance();

        $environment = is_null($environment) ? Config::read("environment") : $environment;

        if (isset($self->config[$environment])) {
            $config = $self->config[$environment];
        } else {
            trigger_error("Não pode ser encontrado as configurações do banco de dados. Verifique /app/config/database.php", E_USER_ERROR);
            return false;
        }
        $datasource = Inflector::camelize("{$config['driver']}_datasource");
        if (isset($self->datasources[$environment])) {
            return $self->datasources[$environment];
        } elseif (self::loadDatasource($datasource)) {
            $self->datasources[$environment] = new $datasource($config);
            return $self->datasources[$environment];
        } else {
            trigger_error("Não foi possível encontrar {$datasource} datasource", E_USER_ERROR);
            return false;
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
            if (App::path("Datasource", Inflector::camelize($datasource))) {
                App::import("Datasource", Inflector::camelize($datasource));
            }
        }
        return class_exists($datasource);
    }

}

?>