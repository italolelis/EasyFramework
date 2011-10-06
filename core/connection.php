<?php

/**
 *  Connection é a classe que cuida das conexões com banco de dados no EasyFramework,
 *  encontrando e carregando datasources de acordo com a configuração desejada.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://spaghettiphp.org/)
 *
 */
class Connection extends Object {

    /**
     *  Configurações de banco de dados da aplicação.
     */
    private $config = array();

    /**
     *  Datasources já instanciados.
     */
    private $datasources = array();

    /**
     *  Lendo arquivos de configuração do banco de dados.
     */
    public function __construct() {
        $this->config = Config::read("datasource");
    }

    public static function &getInstance() {
        static $instance = array();
        if (!isset($instance[0]) || !$instance[0]):
            $instance[0] = new Connection;
        endif;
        return $instance[0];
    }

    /**
     *  Cria uma instância de um datasource ou retorna outra instância existente.
     *
     *  @param string $environment Configuração de ambiente a ser usada
     *  @return object Instância do datasource
     */
    public static function &getDatasource($environment = null) {
        $self = self::getInstance();
        $environment = is_null($environment) ? Config::read("environment") : $environment;
        if (isset($self->config[$environment])):
            $config = $self->config[$environment];
        else:
            trigger_error("Não pode ser encontrado as configurações do banco de dados. Verifique /app/config/database.php", E_USER_ERROR);
            return false;
        endif;
        $datasource = Inflector::camelize("{$config['driver']}_datasource");
        if (isset($self->datasources[$environment])):
            return $self->datasources[$environment];
        elseif (self::loadDatasource($datasource)):
            $self->datasources[$environment] = new $datasource($config);
            return $self->datasources[$environment];
        else:
            trigger_error("Não foi possível encontrar {$datasource} datasource", E_USER_ERROR);
            return false;
        endif;
    }

    /**
     *  Carrega um datasource.
     *
     *  @param string $datasource Nome do datasource
     *  @return boolean Verdadeiro se o datasource existir e for carregado
     */
    public static function loadDatasource($datasource = null) {
        if (!class_exists($datasource)):
            if (App::path("Datasource", Inflector::underscore($datasource))):
                App::import("Datasource", Inflector::underscore($datasource));
            endif;
        endif;
        return class_exists($datasource);
    }

}

?>