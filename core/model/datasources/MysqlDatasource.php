<?php

/**
 *  MysqlDatasource é a camada de abstração para bancos de dados
 *  MySQL. A classe provê métodos para criação e execução de consultas e retorno
 *  dos respectivos dados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, Easy Framework (http://www.easy.lellysinformatica.com)
 *
 */
class MysqlDatasource extends PdoDatasource {

    /**
     *  Descrição das tabelas do banco de dados.
     */
    protected $schema = array();

    /**
     *  Lista das tabelas contidas no banco de dados.
     */
    protected $sources = array();

    /**
     *  Lista as tabelas existentes no banco de dados.
     *
     *  @return array Lista de tabelas no banco de dados
     */
    public function listSources() {

        $this->sources = Cache::read('sources', '_easy_model_');

        if (empty($this->sources)) {
            $this->query("SHOW TABLES FROM {$this->config['database']}");
            while ($source = $this->fetch_array()) {
                $this->sources [] = $source[0];
            }
            Cache::write('sources', $this->sources, '_easy_model_');
        }
        return $this->sources;
    }

    /**
     *  Descreve uma tabela do banco de dados.
     *
     *  @param string $table Tabela a ser descrita
     *  @return array Descrição da tabela
     */
    public function describe($table) {
        $this->schema[$table] = Cache::read('describe', '_easy_model_');

        if (empty($this->schema[$table])) {
            //if (!isset($this->schema[$table])) {
                $query = $this->query('SHOW COLUMNS FROM ' . $table);
                $columns = $this->fetchAll($query);
                $schema = array();

                foreach ($columns as $column) {
                    $schema[$column->Field] = array(
                        'key' => $column->Key
                    );
                }
                $this->schema[$table] = $schema;
            //}
            Cache::write('describe', $this->schema[$table], '_easy_model_');
        }

        return $this->schema[$table];
    }

    public function renderInsert($params) {
        $sql = 'INSERT INTO ' . $params['table'];
        $sql .= '(' . join(',', $params['fields']) . ')';
        $sql .= ' VALUES(' . join(",", mysql_real_escape_string($params['values'])) . ')';

        return $sql;
    }

    public function renderUpdate($params) {
        $sql = 'UPDATE ' . $params['table'] . ' SET ';

        $updateValues = array();

        foreach ($params['values'] as $field => $value):
            $updateValues [] = $field . "= '" . mysql_real_escape_string($value) . "'";
        endforeach;

        $sql .= join(", ", $updateValues);

        $sql .= $this->renderWhere($params);
        $sql .= $this->renderOrder($params);
        $sql .= $this->renderLimit($params);

        return $sql;
    }

    public function renderSelect($params) {
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

    public function renderDelete($params) {
        $sql = 'DELETE FROM ' . $params['table'];

        $sql .= $this->renderWhere($params);
        $sql .= $this->renderOrder($params);
        $sql .= $this->renderLimit($params);

        return $sql;
    }

    public function renderGroupBy($params) {
        if ($params['groupBy']) {
            return ' GROUP BY ' . $params['groupBy'];
        }
    }

    public function renderHaving($params) {
        if ($params['having']) {
            return ' HAVING ' . $params['having'];
        }
    }

    public function renderOrder($params) {
        if ($params['order']) {
            return ' ORDER BY ' . $this->order($params['order']);
        }
    }

    public function renderLimit($params) {
        if ($params['offset'] || $params['limit']) {
            return' LIMIT ' . $this->limit($params['offset'], $params['limit']);
        }
    }

    public function renderWhere($params) {
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

    public function count($params) {
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

    public function join($params) {
        if (is_array($params)) {
            $params += array(
                'type' => null,
                'on' => null
            );

            $join = 'JOIN ' . $params['table'];

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

    public function order($order) {
        if (is_array($order)) {
            $order = implode(',', $order);
        }

        return $order;
    }

    public function limit($offset, $limit) {
        if (!is_null($offset)) {
            $limit = $limit . ',' . $offset;
        }

        return $limit;
    }

}

?>