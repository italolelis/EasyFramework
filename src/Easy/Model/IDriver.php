<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Model;

use PDO;
use PDOStatement;

interface IDriver
{

    /**
     * Gets the configuration array for the current datasource
     */
    public function getConfig();

    public function connect();

    public function disconnect();

    public function enabled();

    /**
     * Prepares a sql statement to be executed
     *
     * @param string $sql
     * @return Cake\Model\Datasource\Database\Statement
     * */
    public function execute($sql, $values = array());

    /**
     * Starts a transaction
     *
     * @return boolean true on success, false otherwise
     * */
    public function beginTransaction();

    /**
     * Commits a transaction
     *
     * @return boolean true on success, false otherwise
     * */
    public function commit();

    /**
     * Rollsback a transaction
     *
     * @return boolean true on success, false otherwise
     * */
    public function rollback();

    public function fetchAll(PDOStatement $result, $model, $fetchMode = PDO::FETCH_OBJ);

    public function create($table, $data);

    public function read(Query $query, $model = "");

    public function update($table, $values, Query $query = null);

    public function delete($table, Query $query = null);

    public function affectedRows();

    public function lastInsertedId();
}
