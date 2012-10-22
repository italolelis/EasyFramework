<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Model\ORM;

/**
 * This class is responsible for building SQL query strings via an object oriented
 * PHP interface.
 *
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Query
{
    /* The query types. */

    const SELECT = 0;
    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;

    /** The builder states. */
    const STATE_DIRTY = 0;
    const STATE_CLEAN = 1;

    /**
     * @var string Type of this query (select, insert, update, delete)
     */
    protected $type = self::SELECT;

    /**
     * @var integer The state of the query object. Can be dirty or clean.
     */
    protected $state = self::STATE_CLEAN;

    /**
     * @var array The array of SQL parts collected.
     */
    protected $parts = array(
        'distinct' => false,
        'select' => array(),
        'from' => array(),
        'join' => array(),
        'set' => array(),
        'values' => array(),
        'where' => null,
        'group' => array(),
        'having' => null,
        'order' => array(),
        'limit' => array()
    );

    /**
     * @var string The complete DQL string for this query.
     */
    protected $sql;
    protected $conditionsCollection;

    /**
     * Gets the conditions for this query
     * @return Conditions
     */
    public function getConditions()
    {
        return $this->conditionsCollection;
    }

    /**
     * Returns the type of this query (select, insert, update, delete)
     *
     * @return string
     * */
    public function getType()
    {
        return $this->type;
    }

    public function getPart($part)
    {
        return $this->parts[$part];
    }

    public function getParts()
    {
        return $this->parts;
    }

    /**
     * Get the state of this query builder instance.
     *
     * @return integer Either Query::STATE_DIRTY or Query::STATE_CLEAN.
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get the complete SQL string formed by the current specifications of this QueryBuilder.
     *
     * <code>
     *     $qb = new Query()
     *         ->select('u')
     *         ->from('User', 'u')
     *     echo $qb->getSql(); // SELECT u FROM User u
     * </code>
     *
     * @return string The SQL query string.
     */
    public function getSql()
    {
        if ($this->sql !== null && $this->state === self::STATE_CLEAN) {
            return $this->sql;
        }

        $sql = "";

        switch ($this->type) {
            case self::SELECT :
                $sql = $this->_getSQLForSelect();
                break;
            case self::INSERT:
                $sql = $this->_getSQLForInsert();
                break;
            case self::UPDATE:
                $sql = $this->_getSQLForUpdate();
                break;
            case self::DELETE:
                $sql = $this->_getSQLForDelete();
                break;
        }

        $this->state = self::STATE_CLEAN;
        $this->sql = $sql;
        return $sql;
    }

    /**
     * Either appends to or replaces a single, generic query part.
     *
     * The available parts are: 'select', 'from', 'join', 'set', 'where',
     * 'group', 'having' and 'order'.
     *
     * @param string $sqlPartName 
     * @param string $sqlPart 
     * @param string $append 
     * @return Query This Query instance.
     */
    public function add($sqlPartName, $sqlPart, $append = false)
    {
        $isMultiple = is_array($this->parts[$sqlPartName]);

        if ($append && $isMultiple) {
            if (is_array($sqlPart)) {
                $key = key($sqlPart);
                $this->parts[$sqlPartName][$key][] = $sqlPart[$key];
            } else {
                $this->parts[$sqlPartName][] = $sqlPart;
            }
        } else {
            $this->parts[$sqlPartName] = $sqlPart;
        }

        $this->state = self::STATE_DIRTY;
        return $this;
    }

    /**
     * Specifies an item that is to be returned in the query result.
     * Replaces any previously specified selections, if any.
     *
     * <code>
     *     $qb = new Query()
     *         ->select('u', 'p')
     *         ->from('User', 'u')
     *         ->leftJoin('u.Phonenumbers', 'p');
     * </code>
     *
     * @param mixed $select The selection expressions.
     * @return Query This Query instance.
     */
    public function select($select = null)
    {
        if ($select === null) {
            return $this->parts['select'];
        }

        if (is_string($select)) {
            $select = array($select);
        }

        $this->type = self::SELECT;
        $this->add('select', $select);
        return $this;
    }

    /**
     * Add a DISTINCT flag to this query.
     *
     * <code>
     *     $qb = new Query()
     *         ->select('u')
     *         ->distinct()
     *         ->from('User', 'u');
     * </code>
     *
     * @param bool $flag
     * @return Query
     */
    public function distinct($flag = true)
    {
        $this->parts['distinct'] = (bool) $flag;
        return $this;
    }

    /**
     * Adds an item that is to be returned in the query result.
     *
     * <code>
     *     $qb = new Query()
     *         ->select('u')
     *         ->addSelect('p')
     *         ->from('User', 'u')
     *         ->leftJoin('u.Phonenumbers', 'p');
     * </code>
     *
     * @param mixed $select The selection expression.
     * @return Query This Query instance.
     */
    public function addSelect($select = null)
    {
        $this->type = self::SELECT;

        if (empty($select)) {
            return $this;
        }

        $selects = is_array($select) ? $select : func_get_args();

        return $this->add('select', $selects, true);
    }

    public function insert($table)
    {
        $this->type = self::INSERT;
        return $this->add('from', $table);
    }

    /**
     * Turns the query being built into a bulk update query that ranges over
     * a certain entity type.
     *
     * <code>
     *     $qb = new Query()
     *         ->update('User', 'u')
     *         ->set('u.password', md5('password'))
     *         ->where('u.id = ?');
     * </code>
     *
     * @param string $update The class/type whose instances are subject to the update.
     * @param string $alias The class/type alias used in the constructed query.
     * @return Query This Query instance.
     */
    public function update($table)
    {
        $this->type = self::UPDATE;
        return $this->add('from', $table);
    }

    /**
     * Turns the query being built into a bulk delete query that ranges over
     * a certain entity type.
     *
     * <code>
     *     $qb = new Query()
     *         ->delete('User', 'u')
     *         ->where('u.id = :user_id');
     * </code>
     *
     * @param string $delete The class/type whose instances are subject to the deletion.
     * @param string $alias The class/type alias used in the constructed query.
     * @return Query This Query instance.
     */
    public function delete($table, $alias = null)
    {
        $this->type = self::DELETE;
        return $this->add('from', $table);
    }

    /**
     * Sets a new value for a field in a bulk update query.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->update('User', 'u')
     *         ->set('u.password', md5('password'))
     *         ->where('u.id = ?');
     * </code>
     *
     * @param array $values The values to set to an expression
     * @return Query This Query instance.
     */
    public function set($values)
    {
        $keys = array_keys($values);
        $values = array_map(function($k) {
                    return $k . ' = ?';
                }, $keys);
        return $this->add('set', $values);
    }

    /**
     * Sets a new value for a field in a bulk update query.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->insert('User', 'u')
     *         ->values('u.password', md5('password'))
     *         ->where('u.id = ?');
     * </code>
     *
     * @param array $values The values to set to an expression
     * @return Query This Query instance.
     */
    public function values($values)
    {
        return $this->add('values', array_keys($values));
    }

    /**
     * Create and add a query root corresponding to the entity identified by the given alias,
     * forming a cartesian product with any existing query roots.
     *
     * <code>
     *     $qb = new Query()
     *         ->select('u')
     *         ->from('User', 'u')
     * </code>
     *
     * @param string $from   The class name.
     * @param string $alias  The alias of the class.
     * @return Query This Query instance.
     */
    public function from($tables = null, $alias = null)
    {
        if (empty($tables)) {
            return $this->parts['from'];
        }

        return $this->add('from', $tables, true);
    }

    /**
     * Creates and adds a join over an entity association to the query.
     *
     * The entities in the joined association will be fetched as part of the query
     * result if the alias used for the joined association is placed in the select
     * expressions.
     *
     *     [php]
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->join('u.Phonenumbers', 'p', 'p.is_primary = 1');
     *
     * @param string $join The relationship to join
     * @param string $alias The alias of the join
     * @param string $on The condition for the join
     * @param string $indexBy The index for the join
     * @return Query This Query instance.
     */
    public function join($join, $alias, $on = null, $indexBy = null)
    {
        $join = 'JOIN' . $join . ' ON ' . $on;
        return $this->add('join', $join, true);
    }

    /**
     * Creates and adds a join over an entity association to the query.
     *
     * The entities in the joined association will be fetched as part of the query
     * result if the alias used for the joined association is placed in the select
     * expressions.
     *
     *     [php]
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->innerJoin('u.Phonenumbers', 'p', 'p.is_primary = 1');
     *
     * @param string $join The relationship to join
     * @param string $alias The alias of the join
     * @param string $on The condition for the join
     * @param string $indexBy The index for the join
     * @return Query This Query instance.
     */
    public function innerJoin($join, $alias, $on = null, $indexBy = null)
    {
        $join = 'INNER JOIN' . $join . ' ON ' . $on;
        return $this->add('join', $join, true);
    }

    /**
     * Creates and adds a join over an entity association to the query.
     *
     * The entities in the joined association will be fetched as part of the query
     * result if the alias used for the joined association is placed in the select
     * expressions.
     *
     *     [php]
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->leftJoin('u.Phonenumbers', 'p', 'p.is_primary = 1');
     *
     * @param string $join The relationship to join
     * @param string $alias The alias of the join
     * @param string $on The condition for the join
     * @param string $indexBy The index for the join
     * @return Query This Query instance.
     */
    public function leftJoin($join, $alias, $on = null, $indexBy = null)
    {
        $join = 'LEFT JOIN' . $join . ' ON ' . $on;
        return $this->add('join', $join, true);
    }

    public function where(Conditions $conditions = null)
    {
        if ($conditions === null) {
            return $this->parts['where'];
        }
        $this->conditionsCollection = $conditions;
        return $this->add('where', $conditions->getKeys());
    }

    public function andWhere(Conditions $conditions)
    {
        $this->conditionsCollection = $conditions;
        $where = $this->getPart('having') . " AND " . $conditions->getKeys();
        return $this->add('where', $where);
    }

    public function orWhere(Conditions $conditions)
    {
        $this->conditionsCollection = $conditions;
        $where = $this->getPart('having') . " OR " . $conditions->getKeys();
        return $this->add('where', $where);
    }

    public function order($order)
    {
        return $this->add('order', $order);
    }

    public function addOrder($order)
    {
        return $this->add('order', $order, true);
    }

    public function group($group)
    {
        return $this->add('group', $group);
    }

    public function addGroup($group)
    {
        return $this->add('group', $group, true);
    }

    public function having($having)
    {
        return $this->add('having', $having);
    }

    public function andHaving($having)
    {
        $having = $this->getPart('having') . " AND " . $having;
        return $this->add('having', $having, true);
    }

    public function orHaving($having)
    {
        $having = $this->getPart('having') . " OR " . $having;
        return $this->add('having', $having, true);
    }

    public function limit($num)
    {
        return $this->add('limit', $num, true);
    }

    public function offset($num = null)
    {
        return $this->add('limit', $num, true);
    }

    private function _getSQLForInsert()
    {
        $statement = sprintf(
                'INSERT INTO %s (%s) VALUES(%s) ', $this->parts['from'], implode(',', $this->parts['values']), implode(',', array_fill(0, count($this->parts['values']), '?')
                )
        );
        return $statement;
    }

    private function _getSQLForDelete()
    {
        return 'DELETE'
                . $this->_getReducedSQLQueryPart('from', array('pre' => ' FROM ', 'separator' => ', '))
                . $this->_getReducedSQLQueryPart('where', array('pre' => ' WHERE '))
                . $this->_getReducedSQLQueryPart('order', array('pre' => ' ORDER BY ', 'separator' => ', '));
    }

    private function _getSQLForUpdate()
    {
        return 'UPDATE'
                . $this->_getReducedSQLQueryPart('from', array('pre' => ' ', 'separator' => ', '))
                . $this->_getReducedSQLQueryPart('set', array('pre' => ' SET ', 'separator' => ', '))
                . $this->_getReducedSQLQueryPart('where', array('pre' => ' WHERE '))
                . $this->_getReducedSQLQueryPart('order', array('pre' => ' ORDER BY ', 'separator' => ', '));
    }

    private function _getSQLForSelect()
    {
        $sql = 'SELECT'
                . ($this->parts['distinct'] === true ? ' DISTINCT' : '')
                . $this->_getReducedSQLQueryPart('select', array('pre' => ' ', 'separator' => ', '));

        $joins = $this->getPart('join');
        $joinsString = "";
        if (!empty($joins)) {
            $joinsString = implode(" ", $joins) . " ";
        }

        $sql .= $this->_getReducedSQLQueryPart('from', array('pre' => ' FROM ', 'separator' => ', '))
                . $joinsString
                . $this->_getReducedSQLQueryPart('where', array('pre' => ' WHERE '))
                . $this->_getReducedSQLQueryPart('group', array('pre' => ' GROUP BY ', 'separator' => ', '))
                . $this->_getReducedSQLQueryPart('having', array('pre' => ' HAVING '))
                . $this->_getReducedSQLQueryPart('order', array('pre' => ' ORDER BY ', 'separator' => ', '))
                . $this->_getReducedSQLQueryPart('limit', array('pre' => ' LIMIT ', 'separator' => ', '));

        return $sql;
    }

    private function _getReducedSQLQueryPart($queryPartName, $options = array())
    {
        $queryPart = $this->getPart($queryPartName);

        if (empty($queryPart)) {
            return (isset($options['empty']) ? $options['empty'] : '');
        }

        return (isset($options['pre']) ? $options['pre'] : '')
                . (is_array($queryPart) ? implode($options['separator'], $queryPart) : $queryPart)
                . (isset($options['post']) ? $options['post'] : '');
    }

    /**
     * Returns string respresentation of this query (complete SQL statement)
     *
     * @return string
     * */
    public function __toString()
    {
        return $this->getSql();
    }

}
