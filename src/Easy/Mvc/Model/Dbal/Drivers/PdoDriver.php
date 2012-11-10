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

namespace Easy\Mvc\Model\Dbal\Drivers;

use Easy\Mvc\Model\Dbal\IDriver;
use Easy\Mvc\Model\ORM\Query;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * This class is responsible for the basic PDO functions
 *
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class PdoDriver implements IDriver
{

    /**
     *  Conexão utilizada pelo banco de dados.
     * @var PDO
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
     * @var PDOStatement
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

    public static function enabled()
    {
        throw new RuntimeException(__("This method must be implemented on subclass"));
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

    public function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;
    }

    public function fetchAll(PDOStatement $result, $model = null, $fetchMode = PDO::FETCH_CLASS)
    {
        if (!empty($model) && $fetchMode === PDO::FETCH_CLASS) {
            return $result->fetchAll($fetchMode, $model);
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
        $query = new Query();
        $query->insert($table)
                ->values($data);
        return $this->execute($query->getSql(), array_values($data));
    }

    public function read(Query $query, $model = null)
    {
        $values = array();

        if (!$query->select()) {
            $query->select("*");
        }

        if ($query->getConditions() !== null) {
            $values = $query->getConditions()->getValues();
        }
        $query = $this->execute($query->getSql(), $values);

        $fetchedResult = $this->fetchAll($query, $model);

        return $fetchedResult;
    }

    public function update($table, $values, Query $query = null)
    {
        if ($query === null) {
            $query = new Query();
        }
        $query->update($table)
                ->set($values);
        $values = array_merge(array_values($values), $query->getConditions()->getValues());
        return $this->execute($query->getSql(), $values);
    }

    public function delete($table, Query $query = null)
    {
        if ($query === null) {
            $query = new Query();
        }

        $query->delete($table);
        $values = $query->getConditions()->getValues();
        return $this->execute($query->getSql(), $values);
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

    public function listColumns($table)
    {
        throw new RuntimeException(__("This method must be implemented on subclass"));
    }

}

