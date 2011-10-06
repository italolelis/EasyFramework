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
abstract class Model extends Object implements ICrud {

    /**
     * Tabela do banco de dados
     * @var string 
     */
    protected $table;

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
            $object_array[] = $this->inicialize($row);
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

    function update() {
        return;
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
