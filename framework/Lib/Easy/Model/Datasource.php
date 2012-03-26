<?php

/**
 *  Datasource é o reposnsável pela conexão com o banco de dados, gerenciando
 *  o estado da conexão com o banco de dados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
abstract class Datasource {

    /**
     * Are we connected to the DataSource?
     *
     * @var boolean
     */
    protected $connected = false;

    /**
     *  Conexão utilizada pelo banco de dados.
     */
    protected $connection;

    public function __construct($config) {
        $this->config = $config;
    }

    public function connect() {
        return false;
    }

    public function disconnect() {
        return false;
    }

    public function query($sql = null) {
        return false;
    }

    /**
     * Used to create new records. The "C" CRUD.
     *
     * To-be-overridden in subclasses.
     *
     * @param Model $model The Model to be created.
     * @param array $fields An Array of fields to be saved.
     * @param array $values An Array of values to save.
     * @return boolean success
     */
    public function create($params = array()) {
        return false;
    }

    /**
     * Used to read records from the Datasource. The "R" in CRUD
     *
     * To-be-overridden in subclasses.
     *
     * @param Model $model The model being read.
     * @param array $queryData An array of query data used to find the data you want
     * @return mixed
     */
    public function read($params) {
        return false;
    }

    /**
     * Update a record(s) in the datasource.
     *
     * To-be-overridden in subclasses.
     *
     * @param Model $model Instance of the model class being updated
     * @param array $fields Array of fields to be updated
     * @param array $values Array of values to be update $fields to.
     * @return boolean Success
     */
    public function update($params = array()) {
        return false;
    }

    /**
     * Delete a record(s) in the datasource.
     *
     * To-be-overridden in subclasses.
     *
     * @param Model $model The model class having record(s) deleted
     * @param mixed $conditions The conditions to use for deleting.
     * @return void
     */
    public function delete($params = array()) {
        return false;
    }

    public function __destruct() {
        if ($this->connected) {
            $this->disconnect();
        }
    }

}

?>