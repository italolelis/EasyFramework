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

namespace Easy\Model\Datasources;

use Easy\Model\Datasources\PdoDatasource;
use Easy\Utility\Hash;
use \PDO;

class MysqlDatasource extends PdoDatasource
{

    protected $_baseConfig = [
        'persistent' => true,
        'host' => 'localhost',
        'login' => 'root',
        'password' => '',
        'database' => 'easy',
        'port' => '3306',
        'flags' => array(),
        'encoding' => 'utf8',
        'dsn' => null
    ];

    public function connect()
    {
        $config = Hash::merge($this->_baseConfig, $this->config);

        if (!$this->connection) {
            try {

                if (empty($config['dsn'])) {
                    if (empty($config['unix_socket'])) {
                        $config['dsn'] = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['encoding']}";
                    } else {
                        $config['dsn'] = "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
                    }
                }

                $config['flags'] += array(
                    PDO::ATTR_PERSISTENT => $config['persistent'],
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                );

//                if (!empty($config['encoding'])) {
//                    $flags[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $config['encoding'];
//                }

                $this->connection = new PDO(
                                $config['dsn'],
                                $config['user'],
                                $config['password'],
                                $config['flags']
                );
                $this->connected = true;
            } catch (\PDOException $e) {
                throw new Error\MissingConnectionException(array('class' => $e->getMessage()));
            }
        }

        return $this->connection;
    }

    /**
     * Check whether the MySQL extension is installed/loaded
     *
     * @return boolean
     */
    public function enabled()
    {
        return in_array('mysql', PDO::getAvailableDrivers());
    }

    /**
     * Sets the database encoding
     *
     * @param string $enc Database encoding
     * @return boolean
     */
    public function setEncoding($enc)
    {
        return $this->query('SET NAMES ' . $enc) !== false;
    }

    public function count($params)
    {
        $fields = '*';

        if (is_array($params)) {
            if (array_key_exists('fields', $params)) {
                $fields = $params['fields'];

                if (is_array($params['fields'])) {
                    $fields = $fields[0];
                }
            }
        }

        $params['fields'] = 'COUNT(' . $fields . ') AS count';

        $results = $this->read($params);
        return $results[0]->count;
    }

    public function listSources()
    {
        $query = $this->connection->prepare('SHOW TABLES FROM ' . $this->config['database']);
        $query->setFetchMode(PDO::FETCH_NUM);
        $query->execute();

        while ($source = $query->fetch()) {
            $sources [] = $source[0];
        }

        return $sources;
    }

    public function describe($table)
    {
        $result = $this->query('SHOW COLUMNS FROM ' . $table);
        $columns = $this->fetchAll($result, null, PDO::FETCH_ASSOC);
        $schema = array();

        foreach ($columns as $column) {
            $schema[$column['Field']] = array(
                'key' => $column['Key']
            );
        }

        return $schemas[$table] = $schema;
    }

    public function renderInsert($params)
    {
        $fields = array_keys($params['data']);
        $sql = 'INSERT INTO %s (%s) VALUES(%s)';
        $sql = sprintf(
                $sql, $params['table'], implode(',', $fields), implode(',', array_fill(0, count($fields), '?'))
        );
        return $sql;
    }

    public function renderUpdate($params)
    {
        $fields = array_keys($params['values']);
        $conditions = $this->renderWhere($params);

        $sql = 'UPDATE %s SET %s %s';
        $sql = sprintf(
                $sql, $params['table'], implode(', ', array_map(function($k) {
                                    return $k . ' = ?';
                                }, $fields)), $conditions
        );

        $sql .= $this->renderLimit($params);
        return $sql;
    }

    public function renderSelect($params)
    {
        $fields = "*";

        if (is_array($params['fields']) && !empty($params['fields'])) {
            $fields = implode(', ', $params['fields']);
        } elseif (is_string($params['fields'])) {
            $fields = $params['fields'];
        }

        $sql = 'SELECT ' . $fields;
        $sql .= ' FROM ' . $params['table'];

        if (is_array($params['joins']) && !empty($params['joins'])) {
            foreach ($params['joins'] as $join) {
                $sql .= ' ' . $this->join($join);
            }
        } elseif (is_string($params['joins'])) {
            $sql .= ' ' . $params['joins'];
        }

        $sql .= $this->renderWhere($params);
        $sql .= $this->renderGroupBy($params);
        $sql .= $this->renderHaving($params);
        $sql .= $this->renderOrder($params);
        $sql .= $this->renderLimit($params);

        return $sql;
    }

    public function renderDelete($params)
    {
        $sql = 'DELETE FROM %' . $params['table'];
        $sql = 'DELETE FROM %s %s';
        $conditions = $this->renderWhere($params);
        $sql = sprintf(
                $sql, $params['table'], $conditions
        );

        $sql .= $this->renderLimit($params);

        return $sql;
    }

    public function renderGroupBy($params)
    {
        if ($params['groupBy']) {
            return ' GROUP BY ' . $params['groupBy'];
        }
    }

    public function renderHaving($params)
    {
        if ($params['having']) {
            return ' HAVING ' . $params['having'];
        }
    }

    public function renderOrder($params)
    {
        if (isset($params['order']) && !empty($params['order'])) {
            return ' ORDER BY ' . $this->order($params['order']);
        }
    }

    public function renderLimit($params)
    {
        if ($params['offset'] || $params['limit']) {
            return' LIMIT ' . $this->limit($params['offset'], $params['limit']);
        }
    }

    public function renderWhere($params)
    {
        if (!empty($params['conditions'])) {
            if (is_array($params['conditions'])) {
                $conditions = join(', ', $params['conditions']);
            } elseif (is_string($params['conditions'])) {
                $conditions = $params['conditions'];
            }
            return ' WHERE ' . $conditions;
        }
        return "";
    }

    public function limit($offset, $limit)
    {
        if (!is_null($offset)) {
            $limit = $limit . ', ' . $offset;
        }

        return $limit;
    }

}