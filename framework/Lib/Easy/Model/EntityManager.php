<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
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
class EntityManager extends Object {

    const FIND_FIRST = 'first';
    const FIND_ALL = 'all';

    /**
     * The name of the DataSource connection that this Model uses
     *
     * The value must be an attribute name that you defined in `app/Config/database.yaml`
     * or created using `ConnectionManager::create()`.
     *
     * @var string
     */
    public $useDbConfig = 'default';

    /**
     * Container for the data that this model gets from persistent storage (usually, a database).
     *
     * @var array
     */
    public $data = array();

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
    protected $model;

    function __construct($model) {
        $this->connection = ConnectionManager::getDataSource($this->useDbConfig);
        $this->model = $model;
        $this->useTable = Table::load($this);
    }

    public function getModel() {
        return $this->model;
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
        return $this->useTable->getName($this->model);
    }

    public function schema() {
        return $this->useTable->schema();
    }

    public function primaryKey() {
        return $this->useTable->primaryKey();
    }

    /**
     * Returns the current record's ID
     *
     * @param integer $list Index on which the composed ID is located
     * @return mixed The ID of the current record, false if no ID
     */
    public function getID($list = 0) {
        if (empty($this->id) || (is_array($this->id) && isset($this->id[0]) && empty($this->id[0]))) {
            return false;
        }

        if (!is_array($this->id)) {
            return $this->id;
        }

        if (isset($this->id[$list]) && !empty($this->id[$list])) {
            return $this->id[$list];
        } elseif (isset($this->id[$list])) {
            return false;
        }

        return current($this->id);
    }

    /**
     * Returns the contents of a single field given the supplied conditions, in the
     * supplied order.
     *
     * @param array $query SQL conditions (defaults to NULL)
     * @param EntityManager $type EntityManager constant, FIND_ALL or FIND_FIRST (defaults to FIND_FIRST)
     * @return string field contents, or false if not found
     */
    public function find($query = null, $type = EntityManager::FIND_FIRST) {
        return $this->{strtolower($type)}($query);
    }

    /**
     * Handles the before/after filter logic for find('all') operations.  Only called by Model::find().
     *
     * @param array $params
     * @return array The result array
     * @see EntityManager::find()
     */
    public function all($params = array()) {
        $params += array(
            "table" => $this->getTable()
        );

        $results = $this->connection->read($params, get_class($this->model));
        return $results;
    }

    /**
     * Handles the before/after filter logic for find('first') operations.  Only called by Model::find().
     *
     * @param array $params
     * @return array The result array
     * @see EntityManager::find()
     */
    protected function first($params = array()) {
        $params += array(
            "limit" => 1
        );
        $results = $this->all($params);
        return empty($results) ? null : $results[0];
    }

    /**
     *  Count the registers of a given condition
     *
     *  @param array $params SQL Conditions
     *  @return int The register's count
     */
    public function count($params = array()) {
        $params += array(
            "table" => $this->getTable()
        );
        return $this->connection->count($params);
    }

    /**
     * Initializes the model for writing a new record, loading the default values
     * for those fields that are not defined in $data, and clearing previous validation errors.
     * Especially helpful for saving data in loops.
     *
     * @param mixed $data Data array to insert into the Database.
     * @return boolean True if the recorde was created, otherwise false
     */
    public function insert($data) {
        $params = array(
            "table" => $this->getTable(),
            "data" => $data
        );
        return $this->connection->create($params);
    }

    /**
     * Updates a model records based on a set of conditions.
     *
     * @param array $data Set of fields and values, indexed by fields.
     *    Fields are treated as SQL snippets, to insert literal values manually escape your data.
     * @param mixed $conditions Conditions to match, true for all records
     * @return boolean True on success, false on failure
     */
    function update($data, $conditions) {
        $conditions += array(
            "table" => $this->getTable(),
            "values" => $data
        );
        return $this->connection->update($conditions);
    }

    /**
     * Saves model data (based on white-list, if supplied) to the database. By
     * default, validation occurs before save.
     *
     * @param array $data Data to save.
     * @return boolean On success true, false on failure
     */
    public function save($data) {
        if (is_object($data)) {
            $data = (array) $data;
        }
        $pk = $this->primaryKey();
        // verify if the record exists
        if (array_key_exists($pk, $data) && !is_null($data[$pk])) {
            $exists = true;
        } else {
            $exists = false;
        }

        $success = true;
        $created = false;

        if ($exists) {
            $data = array_intersect_key($data, $this->schema());

            $success = (bool) $this->update($data, array(
                        "conditions" => array($pk => $data[$pk]),
                        "limit" => 1
                            ));
        } else {
            if (!$this->insert($data)) {
                $success = $created = false;
            } else {
                $created = true;
            }
        }

        return $success;
    }

    /**
     * Removes record for given ID. If no ID is given, the current ID is used. Returns true on success.
     *
     * @param long $id ID of record to delete
     * @param boolean $cascade Set to true to delete records that depend on this record
     * @return boolean True on success
     */
    public function delete($id) {
        $params = array(
            "table" => $this->getTable(),
            "conditions" => array($this->primaryKey() => $id)
        );

        if ($this->connection->delete($params)) {
            return true;
        }
        return false;
    }

    /**
     * Returns true if a record with particular ID exists.
     *
     * If $id is not passed it calls Model::getID() to obtain the current record ID,
     * and then performs a Model::find('count') on the currently configured datasource
     * to ascertain the existence of the record in persistent storage.
     *
     * @param mixed $id ID of record to check for existence
     * @return boolean True if such a record exists
     */
    public function exists($id = null) {
        if ($id === null) {
            $id = $this->getID();
        }
        if ($id === false) {
            return false;
        }

        $conditions = array($this->primaryKey() => $id);
        $query = array('conditions' => $conditions);
        return ($this->count($query) > 0);
    }

    /**
     * Returns a list of fields from the database, and sets the current model
     * data (Model::$data) with the record found.
     *
     * @param mixed $fields String of single field name, or an array of field names.
     * @param mixed $id The ID of the record to read
     * @return array Array of database fields, or false if not found
     */
    public function read($fields = null, $id = null) {
        $this->validationErrors = array();

        if ($id != null) {
            $this->id = $id;
        }

        $id = $this->id;

        if (is_array($this->id)) {
            $id = $this->id[0];
        }

        if ($id !== null && $id !== false) {
            $this->data = $this->find(array(
                'conditions' => array($this->primaryKey() => $id),
                'fields' => $fields
                    ));
            return $this->data;
        } else {
            return false;
        }
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

    /**
     * Called before each find operation. Return false if you want to halt the find
     * call, otherwise return the (modified) query data.
     *
     * @param array $queryData Data used to execute this query, i.e. conditions, order, etc.
     * @return mixed true if the operation should continue, false if it should abort; or, modified
     *               $queryData to continue with new $queryData
     */
    public function beforeFind($queryData) {
        return true;
    }

    /**
     * Called after each find operation. Can be used to modify any results returned by find().
     * Return value should be the (modified) results.
     *
     * @param mixed $results The results of the find operation
     * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
     * @return mixed Result of the find operation
     */
    public function afterFind($results, $primary = false) {
        return $results;
    }

    /**
     * Called before each save operation, after validation. Return a non-true result
     * to halt the save.
     *
     * @param array $options
     * @return boolean True if the operation should continue, false if it should abort
     */
    public function beforeSave($options = array()) {
        return true;
    }

    /**
     * Called after each successful save operation.
     *
     * @param boolean $created True if this save created a new record
     * @return void
     */
    public function afterSave($created) {
        
    }

    /**
     * Called before every deletion operation.
     *
     * @param boolean $cascade If true records that depend on this record will also be deleted
     * @return boolean True if the operation should continue, false if it should abort
     */
    public function beforeDelete($cascade = true) {
        return true;
    }

    /**
     * Called after every deletion operation.
     *
     * @return void
     */
    public function afterDelete() {
        
    }

    /**
     * Called during validation operations, before validation. Please note that custom
     * validation rules can be defined in $validate.
     *
     * @param array $options Options passed from model::save(), see $options of model::save().
     * @return boolean True if validate operation should continue, false to abort
     */
    public function beforeValidate($options = array()) {
        return true;
    }

}

