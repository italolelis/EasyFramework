<?php

/**
 *  Model é o responsável pela camada de dados da aplicação, fazendo a comunicação
 *  com o banco de dados através de uma camada de abstração. Possui funcionalidades
 *  CRUD, além de cuidar dos relacionamentos entre outros models.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
abstract class Model extends Object {

    /**
     *  ID do último registro inserido/alterado.
     */
    public $id = null;

    /**
     *  Estrutura da tabela do modelo.
     */
    public $schema = array();

    /**
     *  Nome da tabela usada pelo modelo.
     */
    public $table = null;

    /**
     *  Campo de chave primária.
     */
    public $primaryKey = null;

    /**
     *  Ordenação padrão para o modelo.
     */
    public $order = null;

    /**
     *  Limite padrão para o modelo.
     */
    public $limit = null;

    /**
     *  Condições padrão para o modelo.
     */
    public $conditions = array();

    /**
     *  Configuração de ambiente a ser usada.
     */
    public $environment = null;

    public function __construct() {
        if (is_null($this->environment)) {
            $this->environment = Config::read("environment");
        }
    }

    /**
     *  Define a tabela a ser usada pelo modelo.
     *
     *  @param string $table Nome da tabela a ser usada
     *  @return boolean Verdadeiro caso a tabela exista
     */
    public function setSource($table) {
        $db = & self::getConnection($this->environment);
        if ($table) {
            $this->table = $table;
            $sources = $db->listSources();
            if (!in_array($this->table, $sources)) {
                $this->error("missingTable", array("model" => get_class($this), "table" => $this->table));
                return false;
            }
            if (empty($this->schema)) {
                $this->describe();
            }
        }
        return true;
    }

    /**
     *  Descreve a tabela do banco de dados.
     *
     *  @return array Descrição da tabela do banco de dados
     */
    public function describe() {
        $db = & self::getConnection($this->environment);
        $schema = $db->describe($this->table);
        if (is_null($this->primaryKey)) {
            foreach ($schema as $field => $describe) {
                if ($describe["key"] == "PRI") {
                    $this->primaryKey = $field;
                    break;
                }
            }
        }
        return $this->schema = $schema;
    }

    /**
     *  Retorna o datasource em uso.
     *
     *  @return object Datasource em uso
     */
    public static function &getConnection($environment = null) {
        static $instance = array();
        if (!isset($instance[0]) || !$instance[0]) {
            $instance[0] = Connection::getDatasource($environment);
        }
        return $instance[0];
    }

    /**
     *  Busca registros no banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na busca
     *  @return array Resultados da busca
     */
    public function all($params = array()) {
        //TODO: ao não passar nada como parâmetro, por padrão precisamos criar um SELECT * FROM
        $db = & self::getConnection($this->environment);
        $params = array_merge(
                array(
            "fields" => array_keys($this->schema),
            "join" => isset($params['join']) ? $params['join'] : null,
            "conditions" => isset($params['conditions']) ? array_merge($this->conditions, $params['conditions']) : $this->conditions,
            "order" => $this->order,
            "groupBy" => isset($params['groupBy']) ? $params['groupBy'] : null,
            "limit" => $this->limit
                ), $params
        );
        $results = $db->read($this->table, $params);
        return $results;
    }

    /**
     *  Busca o primeiro registro no banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na busca
     *  @return array Resultados da busca
     */
    public function first($params = array()) {
        $params = array_merge(array("limit" => 1), $params);
        $results = $this->all($params);
        return empty($results) ? array() : $results[0];
    }

    /**
     *  Conta registros no banco de dados.
     *
     *  @param array $params Parâmetros da busca
     *  @return integer Quantidade de registros encontrados
     */
    public function count($params = array()) {
        $db = & self::getConnection($this->environment);
        $params = array_merge(
                array(
            "fields" => "*",
            "join" => isset($params['join']) ? $params['join'] : null,
            "conditions" => isset($params['conditions']) ? array_merge($this->conditions, $params['conditions']) : $this->conditions,
            "order" => $this->order,
            "groupBy" => isset($params['groupBy']) ? $params['groupBy'] : null,
            "limit" => $this->limit,
                ), $params
        );
        return $db->count($this->table, $params);
    }

    /**
     *  Insere um registro no banco de dados.
     *
     *  @param array $data Dados a serem inseridos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function insert($data) {
        $db = & self::getConnection($this->environment);
        return $db->create($this->table, $data);
    }

    function update($params, $data) {
        $db = & self::getConnection($this->environment);
        $params = array_merge(
                array(
            "conditions" => array(),
            "order" => null,
            "limit" => null), $params
        );
        return $db->update($this->table, array_merge($params, compact("data")));
    }

    /**
     *  Salva um registro no banco de dados.
     *
     *  @param array $data Dados a serem salvos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function save($data) {
        if (isset($data['id']) && !is_null($data['id'])) {
            $save = $this->update(array(
                "conditions" => array('id' => $data['id']),
                "limit" => 1
                    ), $data);
        } else {
            $save = $this->insert($data);
        }
        return $save;
    }

    /**
     *  Apaga registros do banco de dados.
     *
     *  @param array $id Parâmetros a serem usados na operação
     *  @return boolean Verdadeiro caso os registros tenham sido apagados.
     */
    public function delete($id) {
        $db = & self::getConnection($this->environment);
        $params = array(
            "conditions" => array('id' => $id),
            "order" => $this->order,
            "limit" => 1
        );
        return $db->delete($this->table, $params);
    }

    public function getAffectedRows() {
        $db = & self::getConnection($this->environment);
        return $db->affected_rows();
    }

    public function fetch_array() {
        $db = & self::getConnection($this->environment);
        return $db->fetch_array();
    }

    public function fetch_assoc($result = null) {
        $db = & self::getConnection($this->environment);
        return $db->fetch_assoc($result);
    }

    public function fetch_object() {
        $db = & self::getConnection($this->environment);
        return $db->fetch_object();
    }

    /**
     *  Executa uma consulta diretamente no datasource.
     *
     *  @param string $query Consulta a ser executada
     *  @return mixed Resultado da consulta
     */
    public function query($query) {
        $db = & self::getConnection($this->environment);
        return $db->query($query);
    }

    /**
     * Converte uma data para o formato do MySQL
     * 
     * @param string $data
     * @return string 
     */
    function converter_data($data) {
        return date('Y-m-d', strtotime(str_replace("/", "-", $data)));
    }

}

?>
