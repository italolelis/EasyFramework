<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\Dbal;

use Easy\Mvc\Model\ORM\Query;
use PDOStatement;

/**
 * Interface for drivers
 * 
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
interface IDriver
{

    /**
     * Gets the configuration array for the current datasource
     */
    public function getConfig();

    /**
     * Connect to a driver
     * @return boolean True if connected, false otherwise
     */
    public function connect();

    /**
     * Disconnect from driver
     * @return boolean True if disconnected, false otherwise
     */
    public function disconnect();

    /**
     * Check to see if the current driver is enabled on server
     * @return boolean True if is avalaible, false otherwise
     */
    public static function enabled();

    /**
     * Prepares a sql statement to be executed
     *
     * @param string $sql
     * @return Cake\Model\Datasource\Database\Statement
     * */
    public function execute($sql, $values = array());

    /**
     * Starts a transaction
     * @return boolean true on success, false otherwise
     */
    public function beginTransaction();

    /**
     * Commits a transaction
     * @return boolean true on success, false otherwise
     */
    public function commit();

    /**
     * Rollsback a transaction
     * @return boolean true on success, false otherwise
     */
    public function rollback();

    /**
     * Fetches the result statement for some fecth mode
     * @param PDOStatement $result The result set
     * @param string $model The entity to retrun case fetch mode is PDO::FETCH_CLASS
     */
    public function fetchAll(PDOStatement $result, $model);

    public function create($table, $data);

    public function read(Query $query, $model = null);

    public function update($table, $values, Query $query = null);

    public function delete($table, Query $query = null);

    public function affectedRows();

    public function lastInsertedId();

    public function listColumns($table);
}
