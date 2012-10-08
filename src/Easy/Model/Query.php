<?php

namespace Easy\Model;

use Iterator;
use IteratorAggregate;

class Query implements IteratorAggregate
{

    /**
     * Type of this query (select, insert, update, delete)
     *
     * @var string
     * */
    protected $type;

    /**
     * List of SQL parts that will be used to build this query
     *
     * @var array
     * */
    protected $parts = array(
        'select' => array(),
        'from' => array(),
        'join' => array(),
        //'set' => array(),
        'values' => array(),
        'where' => array(),
        'group' => array(),
        'having' => array(),
        'order' => array(),
        'limit' => null,
        'offset' => null
    );
    protected $conditionsCollection;

    /**
     * Indicates wheter internal state of this query was changed and most recent
     * results 
     *
     * @return void
     * */
    protected $dirty = false;

    /**
     * Iterator for statement results
     *
     * @var Iterator
     * */
    protected $_iterator;

    /**
     * Gets the conditions for this query
     * @return Conditions
     */
    public function getConditions()
    {
        return $this->conditionsCollection;
    }

    public function getDirty()
    {
        return $this->dirty;
    }

    public function sql()
    {

        switch ($this->type) {
            case 'select' :
                return $this->buildSelect();
                break;
            case 'insert':
                return $this->buildInsert();
                break;
            case 'update':
                return $this->buildUpdate();
                break;
            case 'delete':
                return $this->buildDelete();
                break;
        }
    }

    protected function buildInsert()
    {
        $statement = sprintf(
                'INSERT INTO %s (%s) VALUES(%s) ', $this->parts['from'], implode(',', $this->parts['values']), implode(',', array_fill(0, count($this->parts['values']), '?')
                )
        );
        return $statement;
    }

    protected function buildUpdate()
    {
        $keys = array_keys($this->parts['values']);
        $statement = sprintf('UPDATE %s SET %s ', $this->parts['from'], implode(', ', array_map(function($k) {
                                    return $k . ' = ?';
                                }, $keys))
        );

        if (!empty($this->parts['where'])) {
            $statement .= $this->buildWhere();
        }

        if (!empty($this->parts['limit'])) {
            $statement .= $this->buildLimit();
        }

        return $statement;
    }

    protected function buildDelete()
    {
        $statement = sprintf('DELETE FROM %s ', $this->parts['from']);

        if (!empty($this->parts['where'])) {
            $statement .= $this->buildWhere();
        }

        if (!empty($this->parts['limit'])) {
            $statement .= $this->buildLimit();
        }

        return $statement;
    }

    protected function buildSelect()
    {
        $statement = sprintf('SELECT %s ', implode(', ', $this->parts['select']));
        if (!empty($this->parts['from'])) {
            $statement .= sprintf('FROM %s ', implode(', ', $this->parts['from']));
        }

        if (!empty($this->parts['join'])) {
            $statement .= $this->buildJoins();
        }

        if (!empty($this->parts['where'])) {
            $statement .= $this->buildWhere();
        }

        if (!empty($this->parts['group'])) {
            $statement .= $this->buildGroup();
        }

        if (!empty($this->parts['having'])) {
            $statement .= $this->buildHaving();
        }

        if (!empty($this->parts['order'])) {
            $statement .= $this->buildOrder();
        }

        if ($this->parts['limit'] !== null) {
            $statement .= $this->buildLimit();
        }

        return $statement;
    }

    protected function buildJoins()
    {
        $joins = '';
        foreach ($this->parts['join'] as $join) {
            $joins .= sprintf(' %s JOIN %s %s', $join['type'], $join['table'], $join['alias']);
            if (!empty($join['conditions'])) {
                $joins .= sprintf(' ON %s', (string) $join['conditions']);
            }
        }
        return trim($joins);
    }

    protected function buildWhere()
    {
        if (is_array($this->parts['where'])) {
            $conditions = join(', ', $this->parts['where']);
        } elseif (is_string($this->parts['where'])) {
            $conditions = $this->parts['where'];
        }

        $statement = sprintf('WHERE %s ', $conditions);

        return $statement;
    }

    protected function buildOrder()
    {
        $statement = sprintf('ORDER BY %s ', $this->parts['order']);
        return $statement;
    }

    protected function buildGroup()
    {
        $statement = sprintf('GROUP BY %s ', $this->parts['group']);
        return $statement;
    }

    protected function buildHaving()
    {
        $statement = sprintf('HAVING %s ', $this->parts['having']);
        return $statement;
    }

    protected function buildLimit()
    {
        if ($this->parts['offset'] !== null) {
            $statement = sprintf('LIMIT %s,%s ', $this->parts['limit'], $this->parts['offset']);
        } else {
            $statement = sprintf('LIMIT %s ', $this->parts['limit']);
        }

        return $statement;
    }

    public function select($fields = null, $overwrite = false)
    {
        if ($fields === null) {
            return $this->parts['select'];
        }

        if (is_string($fields)) {
            $fields = array($fields);
        }

        if ($overwrite) {
            $this->parts['select'] = array_values($fields);
        } else {
            $this->parts['select'] = array_merge($this->parts['select'], array_values($fields));
        }

        $this->dirty = true;
        $this->type = 'select';
        return $this;
    }

    public function insert($table, $data)
    {
        $this->parts['from'] = $table;
        $this->parts['values'] = array_keys($data);
        $this->dirty = true;
        $this->type = 'insert';
        return $this;
    }

    public function update($table, $values)
    {
        $this->parts['from'] = $table;
        $this->parts['values'] = $values;
        $this->dirty = true;
        $this->type = 'update';
        return $this;
    }

    public function delete($table)
    {
        $this->parts['from'] = $table;
        $this->dirty = true;
        $this->type = 'delete';
        return $this;
    }

    public function union($query)
    {
        return $this;
    }

    public function from($tables = array(), $overwrite = false)
    {
        if (empty($tables)) {
            return $this->parts['from'];
        }

        if (is_string($tables)) {
            $tables = [$tables];
        }

        if ($overwrite) {
            $this->parts['from'] = $tables;
        } else {
            $this->parts['from'] = array_merge($this->parts['from'], array_values($tables));
        }
        return $this;
    }

    public function join($tables = array(), $overwrite = false)
    {
        if (empty($tables)) {
            return $this->parts['join'];
        }

        if (is_string($tables) || isset($tables['table'])) {
            $tables = [$tables];
        }

        $joins = array();
        foreach ($tables as $t) {
            if (is_string($t)) {
                $t = array('table' => $t);
            }
            $joins[] = $t + ['type' => 'LEFT', 'alias' => null, 'conditions' => '1 = 1'];
        }

        if ($overwrite) {
            $this->parts['join'] = $joins;
        } else {
            $this->parts['join'] = array_merge($this->parts['join'], array_values($joins));
        }

        $this->dirty = true;
        return $this;
    }

    public function where(Conditions $conditions = null)
    {
        if ($conditions === null) {
            return $this->parts['where'];
        }

        $this->conditionsCollection = $conditions;
        $this->parts['where'] = $conditions->getKeys();
        return $this;
    }

    public function andWhere(Conditions $conditions)
    {
        $this->conditionsCollection = $conditions;

        if (!empty($this->parts['where'])) {
            $this->parts['where'] = sprintf(" AND %s", $conditions->getKeys());
        } else {
            $this->where($conditions);
        }

        return $this;
    }

    public function orWhere(Conditions $conditions)
    {
        $this->conditionsCollection = $conditions;

        if (!empty($this->parts['where'])) {
            $this->parts['where'] = sprintf(" OR %s", $conditions->getKeys());
        } else {
            $this->where($conditions);
        }

        return $this;
    }

    public function order($order, $overwrite = false)
    {
        if ($overwrite) {
            $this->parts['order'] = $order;
        } else {
            if (!empty($this->parts['order'])) {
                $this->parts['order'] .= ", $order";
            } else {
                $this->parts['order'] = $order;
            }
        }
        return $this;
    }

    public function group($group, $overwrite)
    {
        if ($overwrite) {
            $this->parts['group'] = $group;
        } else {
            $this->parts['group'] = array_merge($this->parts['from'], array_values($group));
        }
        return $this;
    }

    public function having($having)
    {
        $this->parts['having'] = $having;
        return $this;
    }

    public function andHaving($having)
    {
        if (!empty($this->parts['having'])) {
            $this->parts['having'] = " AND " . $having;
        } else {
            $this->having($having);
        }

        return $this;
    }

    public function orHaving($having)
    {
        if (!empty($this->parts['having'])) {
            $this->parts['having'] = " OR " . $having;
        } else {
            $this->having($having);
        }
        return $this;
    }

    public function limit($num = null)
    {
        if ($num === null) {
            return $this->parts['limit'];
        }

        $this->parts['limit'] = $num;
        return $this;
    }

    public function offset($num = null)
    {
        if ($num === null) {
            return $this->parts['offset'];
        }

        if ($this->limit() !== null) {
            $this->parts['offset'] = $num;
        }

        return $this;
    }

//    public function distinct($on = array())
//    {
//        return $this;
//    }

    /**
     * Returns the type of this query (select, insert, update, delete)
     *
     * @return string
     * */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Executes this query and returns a results iterator
     *
     * @return Iterator
     * */
    public function getIterator()
    {
        if (empty($this->_iterator)) {
            $this->_iterator = $this->execute();
        }
        return $this->_iterator;
    }

    /**
     * Returns string respresentation of this query (complete SQL statement)
     *
     * @return string
     * */
    public function __toString()
    {
        return $this->sql();
    }

}
