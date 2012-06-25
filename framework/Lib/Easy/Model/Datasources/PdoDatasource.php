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
App::uses('Datasource', 'Model');
App::uses('ValueParser', 'Model/Parser');

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

    public function dsn()
    {
        return $this->config['dsn'];
    }

    public function connect($dsn = null)
    {
        if (!$this->connection) {
            if (is_null($dsn)) {
                $dsn = $this->dsn();
            }
            $this->connection = new PDO($dsn);
            $this->connected = true;
        }

        return $this->connection;
    }

    public function disconnect()
    {
        if ($this->_result instanceof PDOStatement) {
            $this->_result->closeCursor();
        }
        unset($this->_connection);
        $this->connected = false;
        return true;
    }

    public function begin()
    {
        return $this->connection->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function rollback()
    {
        return $this->connection->rollBack();
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
        if (is_object($req) && ($req instanceof PDOStatement)) {
            foreach ($array as $key => $value) {
                if ($typeArray)
                    $req->bindValue($key + 1, $value, $typeArray[$key]);
                else {
                    if (is_int($value))
                        $param = PDO::PARAM_INT;
                    elseif (is_bool($value))
                        $param = PDO::PARAM_BOOL;
                    elseif (is_null($value))
                        $param = PDO::PARAM_NULL;
                    elseif (is_string($value))
                        $param = PDO::PARAM_STR;
                    else
                        $param = FALSE;

                    if ($param)
                        $req->bindValue($key + 1, $value, $param);
                }
            }
        }
    }

    public function fetchAll($result, $model, $fetchMode = PDO::FETCH_OBJ)
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
        //Debugger::dump($sql);
        //Debugger::dump($values);
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

}