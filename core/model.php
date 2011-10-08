<?php

App::import('Lib', 'interfaces');

/**
 *  Model é o responsável pela camada de dados da aplicação, fazendo a comunicação
 *  com o banco de dados através de uma camada de abstração. Possui funcionalidades
 *  CRUD, além de cuidar dos relacionamentos entre outros models.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
abstract class Model extends Object {

    /**
     *  ID do Ãºltimo registro inserido/alterado.
     */
    public $id = null;

    /**
     *  NÃ­vel de recursÃ£o padrÃ£o de consultas.
     */
    public $recursion = 0;

    /**
     *  Estrutura da tabela do modelo.
     */
    public $schema = array();

    /**
     *  Nome da tabela usada pelo modelo.
     */
    public $table = null;

    /**
     *  Campo de chave primÃ¡ria.
     */
    public $primaryKey = null;

    /**
     *  ConfiguraÃ§Ã£o de ambiente a ser usada.
     */
    public $environment = null;

    public function __construct() {
        if (is_null($this->environment)):
            $this->environment = Config::read("environment");
        endif;
    }

    /**
     *  Define a tabela a ser usada pelo modelo.
     *
     *  @param string $table Nome da tabela a ser usada
     *  @return boolean Verdadeiro caso a tabela exista
     */
    public function setSource($table) {
        $db = & self::getConnection($this->environment);
        if ($table):
            $this->table = $table;
            $sources = $db->listSources();
            if (!in_array($this->table, $sources)):
                $this->error("missingTable", array("model" => get_class($this), "table" => $this->table));
                return false;
            endif;
            if (empty($this->schema)):
                $this->describe();
            endif;
        endif;
        return true;
    }

    /**
     *  Descreve a tabela do banco de dados.
     *
     *  @return array DescriÃ§Ã£o da tabela do banco de dados
     */
    public function describe() {
        $db = & self::getConnection($this->environment);
        $schema = $db->describe($this->table);
        if (is_null($this->primaryKey)):
            foreach ($schema as $field => $describe):
                if ($describe["key"] == "PRI"):
                    $this->primaryKey = $field;
                    break;
                endif;
            endforeach;
        endif;
        return $this->schema = $schema;
    }

    /**
     *  Retorna o datasource em uso.
     *
     *  @return object Datasource em uso
     */
    public static function &getConnection($environment = null) {
        static $instance = array();
        if (!isset($instance[0]) || !$instance[0]):
            $instance[0] = Connection::getDatasource($environment);
        endif;
        return $instance[0];
    }

    /**
     * Realiza uma consulta SQL
     * 
     * @param string $sql
     * @return array 
     */
    public function find_by_sql($sql = "") {
        //Executa a consulta
        $this->query($sql);
        //Cria um array que receberá os objetos
        $object_array = array();
        //Percorre o resultado da consulta
        while ($row = $this->fetch_assoc()) {
            //Instância um objeto e coloca no array
            $object_array[] = $row;
        }
        //Retorna o array
        return $object_array;
    }

    /**
     * Realiza a contagem de registros de uma tabela
     * @param array $options Array de opções de filtragem e condições
     * @return int 
     */
    public function count($options) {
        $conditions = "";
        $fields = "id";

        if (isset($options['conditions'])) {
            $conditions = "WHERE {$options['conditions']}";
        }

        if (isset($options['fields'])) {
            $fields = $options['fields'];
        }

        $sql = "SELECT COUNT({$fields}) FROM {$this->table} {$conditions}";
        $this->query($sql);
        $row = $this->fetch_array();
        return $row[0];
    }

    /**
     * Método para inicializar um objeto com os resultados de uma consulta SQL
     */
    function inicialize($result) {
        return;
    }

    function add() {
        return;
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
                array("conditions" => array(), "order" => null, "limit" => null), $params
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
        if (isset($data['id']) && !is_null($data['id'])):
            $save = $this->update(array(
                "conditions" => array('id' => $data['id']),
                "limit" => 1
                    ), $data);
        else:
            $save = $this->insert($data);
        //$this->id = $this->getInsertId();
        endif;
        return $save;
    }

    /**
     * Apaga registros do banco de dados
     * @param array $options Array de opções, como condições para apagar registros
     * @return mixed 
     */
    function delete($options) {
        if (isset($options['conditions']) && is_array($options['conditions'])) {
            $conditions = "WHERE '{$options['conditions']}'";
        } else {
            $conditions = "WHERE id='$options'";
        }

        $sql = "DELETE FROM {$this->table} {$conditions}";
        return $this->query($sql);
    }

    function getAll() {
        return;
    }

    function getById($id) {
        return;
    }

    public function getAffectedRows() {
        $db = & self::getConnection($this->environment);
        return $db->affected_rows();
    }

    public function fetch_array() {
        $db = & self::getConnection($this->environment);
        return $db->fetch_array();
    }

    public function fetch_assoc() {
        $db = & self::getConnection($this->environment);
        return $db->fetch_assoc();
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
