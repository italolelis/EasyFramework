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

namespace Easy\Model\Drivers;

use Easy\Model\IDriver;
use Easy\Model\Query;
use PDO;
use PDOException;
use PDOStatement;

class PdoDriver implements IDriver
{

    /**
     *  Conexão utilizada pelo banco de dados.
     */
    protected $connection;
    protected $config;

    /**
     * Whether a transaction is active in this connection
     * @var boolean
     */
    protected $transactionStarted = false;

    /**
     * Result
     * @var array
     */
    protected $result = null;

    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        $this->connection = new PDO(
                        $this->config['dsn'],
                        $this->config['login'],
                        $this->config['password'],
                        $this->config['flags']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        if ($this->result instanceof PDOStatement) {
            $this->result->closeCursor();
        }
        $this->_connection = null;
        return !$this->_connection;
    }

    public function enabled()
    {
        return false;
    }

    public function lastInsertedId()
    {
        return $this->connection->lastInsertId();
    }

    public function affectedRows()
    {
        return $this->affectedRows;
    }

    public function execute($sql, $values = array())
    {
        $query = $this->connection->prepare($sql);

        $query->setFetchMode(PDO::FETCH_OBJ);

        $this->bindArrayValue($query, $values);

        if ($query->execute()) {
            $this->result = $query;
        }
        $this->affectedRows = $query->rowCount();

        return $this->result;
    }

    public function bindArrayValue($req, $array, $typeArray = false)
    {
        if (is_object($req) && ($req instanceof PDOStatement)) {
            foreach ($array as $key => $value) {
                if ($typeArray) {
                    $req->bindValue($key + 1, $value, $typeArray[$key]);
                } else {
                    if (is_int($value)) {
                        $param = PDO::PARAM_INT;
                    } elseif (is_bool($value)) {
                        $param = PDO::PARAM_BOOL;
                    } elseif (is_null($value)) {
                        $param = PDO::PARAM_NULL;
                    } elseif (is_string($value)) {
                        $param = PDO::PARAM_STR;
                    } else {
                        $param = false;
                    }

                    if ($param !== false) {
                        $req->bindValue($key + 1, $value, $param);
                    }
                }
            }
        }
    }

    public function fetchAll(PDOStatement $result, $model, $fetchMode = PDO::FETCH_OBJ)
    {
        if (!empty($model)) {
            return $result->fetchAll(PDO::FETCH_CLASS, $model);
        }
        return $result->fetchAll($fetchMode);
    }

    public function escape($value)
    {
        if (is_null($value)) {
            return 'NULL';
        } else {
            return $this->connection->quote($value);
        }
    }

    public function create($table, $data)
    {
        $values = array_values($data);
        $query = new Query();
        $query->insert($table, $data);
        return $this->execute($query->sql(), $values);
    }

    public function read(Query $query, $model = "")
    {
        $values = array();

        if (!$query->select()) {
            $query->select("*");
        }

        if ($query->getConditions() !== null) {
            $values = $query->getConditions()->getValues();
        }

        $query = $this->execute($query->sql(), $values);

        $fetchedResult = $this->fetchAll($query, $model);

        return $fetchedResult;
    }

    public function update($table, $values, Query $query = null)
    {
        if ($query === null) {
            $query = new Query();
        }
        //$values = array_merge(array_values($values), $query->getConditions()->getValues());
        $query->update($table, $values);
        $values = array_merge(array_values($values), $query->getConditions()->getValues());
        return $this->execute($query->sql(), $values);
    }

    public function delete($table, Query $query = null)
    {
        if ($query === null) {
            $query = new Query();
        }

        $query->delete($table);
        $values = $query->getConditions()->getValues();
        return $this->execute($query->sql(), $values);
    }

    /**
     * Turns off autocommit mode. While autocommit mode is turned off, changes made to the database 
     * via the PDO object instance are not committed until you end the transaction by calling PDO::commit(). 
     * Calling PDO::rollBack() will roll back all changes to the database and return the connection 
     * to autocommit mode. 
     * Some databases, including MySQL, automatically issue an implicit COMMIT when a database definition 
     * language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a transaction. The implicit 
     * COMMIT will prevent you from rolling back any other changes within the transaction boundary.
     * 
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function beginTransaction()
    {
        if (!$this->transactionStarted) {
            $this->connection->beginTransaction();
            $this->transactionStarted = true;
        }
    }

    /**
     * Commits a transaction, returning the database connection to autocommit mode until the next 
     * call to PDO::beginTransaction() starts a new transaction.
     * 
     * @return bool TRUE on success or FALSE on failure.
     */
    public function commit()
    {
        if (!$this->transactionStarted) {
            return false;
        }
        $this->transactionStarted = false;
        return $this->connection->commit();
    }

    /**
     * Rolls back the current transaction, as initiated by PDO::beginTransaction().
     * If the database was set to autocommit mode, this function will restore autocommit mode 
     * after it has rolled back the transaction. 
     * Some databases, including MySQL, automatically issue an implicit COMMIT when a database 
     * definition language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a 
     * transaction. The implicit COMMIT will prevent you from rolling back any other changes within 
     * the transaction boundary.
     * 
     * @return bool Returns TRUE on success or FALSE on failure.
     * @throws PDOException will be thrown if no transaction is active.
     */
    public function rollBack()
    {
        if (!$this->transactionStarted) {
            return false;
        }
        $this->transactionStarted = false;
        return $this->connection->rollBack();
    }

}

