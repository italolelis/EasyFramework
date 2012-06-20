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
App::uses('PdoDatasource', 'Datasource');

class MysqlDatasource extends PdoDatasource
{

    public function connect($dsn = null, $username = null, $password = null)
    {
        if (!$this->connection) {

            if (is_null($dsn)) {
                $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']}";
                $username = $this->config['user'];
                $password = $this->config['password'];
            }

            $flags = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );
            if (!empty($this->config['encoding'])) {
                $flags[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->config['encoding'];
            }

            $this->connection = new PDO($dsn, $username, $password, $flags);
            $this->connected = true;
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
        $this->sources = Cache::read('sources', '_easy_model_');
        if (empty($this->sources)) {
            $query = $this->connection->prepare('SHOW TABLES FROM ' . $this->config['database']);
            $query->setFetchMode(PDO::FETCH_NUM);
            $query->execute();

            while ($source = $query->fetch()) {
                $this->sources [] = $source[0];
            }
            Cache::write('sources', $this->sources, '_easy_model_');
        }

        return $this->sources;
    }

    public function describe($table)
    {
        $this->schema[$table] = Cache::read('describe', '_easy_model_');
        if (empty($this->schema[$table])) {
            $result = $this->query('SHOW COLUMNS FROM ' . $table);
            $columns = $this->fetchAll($result, null, PDO::FETCH_ASSOC);
            $schema = array();

            foreach ($columns as $column) {
                $schema[$column['Field']] = array(
                    'key' => $column['Key']
                );
            }

            $this->schema[$table] = $schema;
            Cache::write('describe', $this->schema[$table], '_easy_model_');
        }

        return $this->schema[$table];
    }

    public function renderInsert($params)
    {
        $sql = 'INSERT INTO ' . $params['table'];

        $fields = array_keys($params['data']);
        $sql .= '(' . join(',', $fields) . ')';

        $values = rtrim(str_repeat('?,', count($fields)), ',');
        $sql .= ' VALUES(' . $values . ')';

        return $sql;
    }

    public function renderUpdate($params)
    {
        $sql = 'UPDATE ' . $params['table'] . ' SET ';

        $fields = array_keys($params['values']);
        $update_fields = array();

        foreach ($fields as $field) {
            $update_fields [] = $field . ' = ?';
        }

        $sql .= join(', ', $update_fields);


        $sql .= $this->renderWhere($params);
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
        $sql = 'DELETE FROM ' . $params['table'];

        $sql .= $this->renderWhere($params);
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