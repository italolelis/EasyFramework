<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 * @since         EasyFramework v 1.3.5
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Model\Datasources;

use Easy\Model\Datasource;
use Easy\Model\Parser\ValueParser;
use Easy\Error;
use \PDO;

abstract class PdoDatasource extends Datasource
{

    protected $affectedRows;
    protected $lastQuery;
    protected $params = array(
        'fields' => '*',
        'joins' => array(),
        'conditions' => array(),
        'groupBy' => null,
        'having' => null,
        'order' => null,
        'offset' => null,
        'limit' => null
    );

    /**
     * Result
     *
     * @var array
     */
    protected $_result = null;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->connect();
    }

    public function connect()
    {
        throw new Error\NotImplementedException(__("The connection has to be defined by some driver"));
    }

    public function disconnect()
    {
        if ($this->_result instanceof \PDOStatement) {
            $this->_result->closeCursor();
        }
        $this->_connection = null;
        $this->connected = false;
        return !$this->connected;
    }

    public function insertId()
    {
        return $this->connection->lastInsertId();
    }

    public function affectedRows()
    {
        return $this->affectedRows;
    }

    public function alias($fields)
    {
        if (is_array($fields)) {
            if (is_hash($fields)) {
                foreach ($fields as $alias => $field) {
                    if (!is_numeric($alias)) {
                        $fields[$alias] = $field . ' AS ' . $alias;
                    }
                }
            }

            $fields = implode(',', $fields);
        }

        return $fields;
    }

    public function join($params)
    {
        if (is_array($params)) {
            $params += array(
                'type' => null,
                'on' => null
            );

            $join = 'JOIN ' . $this->alias($params['table']);

            if ($params['type']) {
                $join = strtoupper($params['type']) . ' ' . $join;
            }

            if ($params['on']) {
                $join .= ' ON ' . $params['on'];
            }
        } else {
            $join = $params;
        }

        return $join;
    }

    public function order($order)
    {
        if (is_array($order)) {
            $order = implode(',', $order);
        }

        return $order;
    }

    public function logQuery($sql)
    {
        return $this->lastQuery = $sql;
    }

    public function query($sql, $values = array())
    {
        $this->logQuery($sql);
        $query = $this->connection->prepare($sql);

        $query->setFetchMode(PDO::FETCH_OBJ);

        $this->bindArrayValue($query, $values);

        if ($query->execute()) {
            $this->_result = $query;
        }
        $this->affectedRows = $query->rowCount();

        return $this->_result;
    }

    public function bindArrayValue($req, $array, $typeArray = false)
    {
        if (is_object($req) && ($req instanceof \PDOStatement)) {
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

    public function fetchAll(\PDOStatement $result, $model, $fetchMode = PDO::FETCH_OBJ)
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

    public function create($params)
    {
        $params += $this->params;

        $values = array_values($params['data']);
        $sql = $this->renderInsert($params);

        $query = $this->query($sql, $values);

        return $query;
    }

    public function read($params, $model = "")
    {
        $params += $this->params;

        $query = new ValueParser($params['conditions']);
        $params['conditions'] = $query->conditions();
        $values = $query->values();

        $sql = $this->renderSelect($params);
        $query = $this->query($sql, $values);

        $fetchedResult = $this->fetchAll($query, $model);

        return $fetchedResult;
    }

    public function update($params)
    {
        $params += $this->params;
        $query = new ValueParser($params['conditions']);
        $params['conditions'] = $query->conditions();
        $values = array_merge(array_values($params['values']), $query->values());
        $sql = $this->renderUpdate($params);
        $query = $this->query($sql, $values);

        return $query;
    }

    public function delete($params)
    {
        $params += $this->params;

        $query = new ValueParser($params['conditions']);
        $params['conditions'] = $query->conditions();
        $values = $query->values();

        $sql = $this->renderDelete($params);
        $query = $this->query($sql, $values);

        return $query;
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
        if (!$this->_transactionStarted) {
            $this->connection->beginTransaction();
            $this->_transactionStarted = true;
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
        if (!$this->_transactionStarted) {
            return false;
        }
        $this->_transactionStarted = false;
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
        if (!$this->_transactionStarted) {
            return false;
        }
        $this->_transactionStarted = false;
        return $this->connection->rollBack();
    }

}