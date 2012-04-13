<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 * @since         EasyFramework v 0.2
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('ConnectionManager', 'Model');
App::uses('Table', 'Model');
App::uses('Validation', 'Utility');

/**
 * Object-relational mapper.
 *
 * DBO-backed object data model.
 * Automatically selects a database table name based on a pluralized lowercase object class name
 * (i.e. class 'User' => table 'users'; class 'Man' => table 'men')
 * The table is required to have at least 'id auto_increment' primary key.
 *
 * @package Easy.Model
 */
abstract class Model extends Object {

    const FIND_FIRST = 'first';
    const FIND_ALL = 'all';

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
        return $this->connection->insertId();
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

    public function find($type = Model::FIND_FIRST, $query = array()) {
        return $this->{strtolower($type)}($query);
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
        $params += array(
            "limit" => 1
        );
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
            "conditions" => array($this->primaryKey() => $id)
        );
        return $this->connection->delete($params);
    }

    /**
     * Converte uma data para o formato do MySQL
     * 
     * @deprecated since version 1.5.4
     * 
     * @param string $data
     * @return string 
     */
    function converter_data($data) {
        return date('Y-m-d', strtotime(str_replace("/", "-", $data)));
    }

    /**
     *
     * @param string $date A valid Date
     * @param int $days The number of days foward
     * @param int $mounths The number of months foward
     * @param int $years The number of years foward
     * @return string
     * @deprecated since version 1.5.4
     */
    function makeDate($date, $days = 0, $mounths = 0, $years = 0) {
        $date = date('d/m/Y', strtotime($date));
        $date = explode("/", $date);
        return date('d/m/Y', mktime(0, 0, 0, $date[1] + $mounths, $date[0] + $days, $date[2] + $years));
    }

}
