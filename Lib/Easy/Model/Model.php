<?php

App::uses('ConnectionManager', 'Core/Model');
App::uses('Table', 'Core/Model');
App::uses('Validation', 'Core/Common');

/**
 * Object-relational mapper.
 *
 * DBO-backed object data model.
 * Automatically selects a database table name based on a pluralized lowercase object class name
 * (i.e. class 'User' => table 'users'; class 'Man' => table 'men')
 * The table is required to have at least 'id auto_increment' primary key.
 *
 */
abstract class Model {

    /**
     * Table's name for this Model.
     *
     * @var string
     */
    public $table;

    /**
     * Table object.
     *
     * @var string
     */
    protected $useTable;

    /**
     * Connection Datasource object
     *
     * @var object
     */
    protected $connection = false;

    function __construct() {
        $this->connection = ConnectionManager::getDataSource();
        $this->useTable = Table::load($this);
    }

    public function getLastId() {
        return $this->connection->getLastId();
    }

    public function getAffectedRows() {
        return $this->connection->getAffectedRows();
    }

    public function getConnection() {
        return $this->connection;
    }

    public function getTable() {
        return $this->useTable->getName();
    }

    public function schema() {
        return $this->useTable->schema();
    }

    public function primaryKey() {
        return $this->useTable->primaryKey();
    }

    /**
     *  Busca registros no banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na busca
     *  @return array Resultados da busca
     */
    public function all($params = array()) {
        $params += array(
            "table" => $this->getTable()
        );
        $results = $this->connection->read($params);
        return $results;
    }

    /**
     *  Busca o primeiro registro no banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na busca
     *  @return array Resultados da busca
     */
    public function first($params = array()) {
        $params += array("limit" => 1);
        $results = $this->all($params);
        return empty($results) ? null : $results[0];
    }

    /**
     *  Conta registros no banco de dados.
     *
     *  @param array $params Parâmetros da busca
     *  @return integer Quantidade de registros encontrados
     */
    public function count($params = array()) {
        $params += array(
            "table" => $this->getTable()
        );
        return $this->connection->count($params);
    }

    /**
     *  Insere um registro no banco de dados.
     *
     *  @param array $data Dados a serem inseridos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function insert($data) {
        $params = array(
            "table" => $this->getTable(),
            "data" => $data
        );
        return $this->connection->create($params);
    }

    function update($params, $data) {
        $params += array(
            "table" => $this->getTable(),
            "values" => $data
        );
        return $this->connection->update($params);
    }

    /**
     *  Salva um registro no banco de dados.
     *
     *  @param array $data Dados a serem salvos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function save($data) {
        $pk = $this->primaryKey();
        // verify if the record exists
        if (array_key_exists($pk, $data) && !is_null($data[$pk])) {
            $exists = true;
        } else {
            $exists = false;
        }

        if ($exists) {
            $data = array_intersect_key($data, $this->schema());

            $save = $this->update(array(
                "conditions" => array($pk => $data[$pk]),
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
        $params = array(
            "table" => $this->getTable(),
            "conditions" => array("id" => $id)
        );
        return $this->connection->delete($params);
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

    function makeDate($date, $days = 0, $mounths = 0, $years = 0) {
        $date = date('d/m/Y', strtotime($date));
        $date = explode("/", $date);
        return date('d/m/Y', mktime(0, 0, 0, $date[1] + $mounths, $date[0] + $days, $date[2] + $years));
    }

}

?>
