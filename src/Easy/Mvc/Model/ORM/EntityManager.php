<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\ORM;

use Easy\Collections\Collection;
use Easy\Mvc\Model\Dbal\ConnectionManager;
use Easy\Mvc\Model\Dbal\IDriver;
use Easy\Mvc\Model\IModel;
use Easy\Mvc\ObjectResolver;
use InvalidArgumentException;
use PDOException;

/**
 * The EntityManager is the central access point to ORM functionality.
 * 
 * @since 1.5
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class EntityManager
{

    /**
     * The name of the DataSource connection that this Model uses
     *
     * The value must be an attribute name that you defined in `App/Config/database.yaml`
     * or created using `ConnectionManager::create()`.
     *
     * @var string
     */
    public $useDbConfig = 'default';

    /**
     * @var IDriver Connection Datasource object
     */
    protected $driver = false;

    /**
     * The EntityRepository instances.
     *
     * @var array
     */
    private $repositories = array();
    private static $instance;

    private function __construct($config)
    {
        $this->driver = ConnectionManager::getDriver($config, $this->useDbConfig);
    }

    public function getUseCache()
    {
        return $this->useCache;
    }

    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;
    }

    public function getRepository($entityName)
    {
        if (is_object($entityName)) {
            list(, $entityName) = namespaceSplit(get_class($entityName));
        }

        if (isset($this->repositories[$entityName])) {
            return $this->repositories[$entityName];
        }

        $repository = new EntityRepository($entityName, $this->driver);
        $this->repositories[$entityName] = $repository;

        return $repository;
    }

    public static function getInstance($config = null, $environment = null)
    {
        if (static::$instance === null) {
            static::$instance = new EntityManager($config, $environment);
        }
        return static::$instance;
    }

    public function getLastInsertId()
    {
        return $this->driver->getLastInsertId();
    }

    public function getAffectedRows()
    {
        return $this->driver->getAffectedRows();
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function createQuery()
    {
        return new Query();
    }

    /**
     * Returns the contents of a single field given the supplied conditions, in the
     * supplied order.
     *
     * @param array $query SQL conditions (defaults to NULL)
     * @param EntityManager $type EntityManager constant, FIND_ALL or FIND_FIRST (defaults to FIND_FIRST)
     * @return string field contents, or false if not found
     */
    public function find($model, $identifier = null, Query $query = null)
    {
        $repository = $this->getRepository($model);

        if ($query === null) {
            $query = $this->createQuery();
        }

        if (!empty($identifier)) {
            $query->where(new Conditions(array($repository->getTable()->getPrimaryKey() => $identifier)));
            $results = $this->first($model, $query);
        } else {
            $results = $this->all($model, $query);
        }
        if (!$results instanceof Collection) {
            if ($results instanceof IModel) {
                $results->afterFind();
            }
        } else {
            foreach ($results as $result) {
                if ($result instanceof IModel) {
                    $result->afterFind();
                }
            }
        }

        return $results;
    }

    /**
     * Returns the contents of a single field given the supplied conditions, in the
     * supplied order.
     *
     * @param array $query SQL conditions (defaults to NULL)
     * @param EntityManager $type EntityManager constant, FIND_ALL or FIND_FIRST (defaults to FIND_FIRST)
     * @return string field contents, or false if not found
     */
    public function findByQuery($model, Query $query = null)
    {
        return $this->find($model, null, $query);
    }

    /**
     * Returns the contents of a single field given the supplied conditions, in the
     * supplied order.
     *
     * @param array $criteria SQL conditions (defaults to NULL)
     * @param EntityManager $type EntityManager constant, FIND_ALL or FIND_FIRST (defaults to FIND_FIRST)
     * @return string field contents, or false if not found
     */
    public function findBy($model, $criteria = null)
    {
        $query = $this->createQuery();
        $query->where(new Conditions($criteria));
        return $this->find($model, null, $query);
    }

    /**
     * Returns the contents of a single field given the supplied conditions, in the
     * supplied order.
     *
     * @param array $query SQL conditions (defaults to NULL)
     * @param EntityManager $type EntityManager constant, FIND_ALL or FIND_FIRST (defaults to FIND_FIRST)
     * @return string field contents, or false if not found
     */
    public function findOneBy($model, $conditions = null)
    {
        $query = $this->createQuery();
        $query->where(new Conditions($conditions));
        return $this->first($model, $query);
    }

    /**
     * Handles the before/after filter logic for find('all') operations.  Only called by Model::find().
     *
     * @param array $params
     * @return array The result array
     * @see EntityManager::find()
     */
    protected function all($model, Query $query)
    {
        $repository = $this->getRepository($model);
        if (!$query->from()) {
            $query->from($repository->getTable()->getName());
        }

        $results = $this->driver->read($query, $repository->getNamespacedEntityName());

        return new Collection($results);
    }

    /**
     * Handles the before/after filter logic for find('first') operations.  Only called by Model::find().
     *
     * @param array $params
     * @return array The result array
     * @see EntityManager::find()
     */
    protected function first($model, Query $query)
    {
        $query->limit(1);
        $results = $this->all($model, $query);
        return $results->IsEmpty() ? null : $results[0];
    }

    /**
     *  Count the registers of a given condition
     *
     *  @param array $params SQL Conditions
     *  @return int The register's count
     */
    public function count($model, $fields = null, Query $query = null)
    {
        if ($query === null) {
            $query = $this->createQuery();
        }
        if (empty($fields)) {
            $query->select(array("COUNT(*) AS count"));
        } else {
            $query->select(array("COUNT(" . implode($fields) . ") AS count"), true);
        }
        $result = $this->findByQuery($model, $query)->getArray();
        return $result[0]->count;
    }

    public function countBy($model, $conditions)
    {
        $query = $this->createQuery();
        $query->where(new Conditions($conditions));
        return $this->count($model, null, $query);
    }

    /**
     * Initializes the model for writing a new record, loading the default values
     * for those fields that are not defined in $data, and clearing previous validation errors.
     * Especially helpful for saving data in loops.
     *
     * @param mixed $data Data array to insert into the Database.
     * @return boolean True if the recorde was created, otherwise false
     */
    public function insert($repository, $data)
    {
        return $this->driver->create($repository->getTable()->getName(), $data);
    }

    /**
     * Updates a model records based on a set of conditions.
     *
     * @param array $data Set of fields and values, indexed by fields.
     *    Fields are treated as SQL snippets, to insert literal values manually escape your data.
     * @param mixed $query Conditions to match, true for all records
     * @return boolean True on success, false on failure
     */
    public function update($repository, $data, Query $query)
    {
        return $this->driver->update($repository->getTable()->getName(), $data, $query);
    }

    /**
     * Saves model data (based on white-list, if supplied) to the database. By
     * default, validation occurs before save.
     *
     * @param array $model Data to save.
     * @return boolean On success true, false on failure
     */
    public function save(IModel $model, $success = null, $error = null)
    {
        if (!is_object($model)) {
            throw new InvalidArgumentException(__("Can not save a non object"));
        }
        $repository = $this->getRepository($model);
        $pk = $repository->getTable()->getPrimaryKey();
        $model->beforeSave(); //Call the before save method
        // verify if the record exists
        $exists = isset($model->{$pk}) && !empty($model->{$pk});
        $ok = true;

        $resolver = new ObjectResolver($model);
        $data = $resolver->toArray();
        $data = array_intersect_key($data, $repository->getTable()->getColumns());
        if ($exists) {
            $query = $this->createQuery();
            $query->where(new Conditions(array($pk => $data[$pk])))
                    ->limit(1);
            $ok = (bool) $this->update($repository, $data, $query);
        } else {
            $ok = (bool) $this->insert($repository, $data);
        }

        if ($ok) {
            if (is_callable($success)) {
                return $success($model);
            } else {
                return true;
            }
        } else {
            if (is_callable($error)) {
                return $error($model);
            } else {
                return false;
            }
        }
    }

    public function delete(IModel $model)
    {
        $this->getRepository($model);
        //TODO: Implement cascade system
        $cascade = true;

        $pk = $this->getRepository($model)->getTable()->getPrimaryKey();
        $query = $this->createQuery();
        $query->where(new Conditions(array($pk => $model->{$pk})))
                ->limit(1);

        $model->beforeDelete();
        if ($this->driver->delete($this->getRepository($model)->getTable()->getName(), $query)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Turns off autocommit mode. While autocommit mode is turned off, changes made to the database 
     * via the PDO object instance are not committed until you end the transaction by calling EntityManager::commit(). 
     * Calling EntityManager::rollBack() will roll back all changes to the database and return the connection 
     * to autocommit mode. 
     * Some databases, including MySQL, automatically issue an implicit COMMIT when a database definition 
     * language (DDL) statement such as DROP TABLE or CREATE TABLE is issued within a transaction. The implicit 
     * COMMIT will prevent you from rolling back any other changes within the transaction boundary.
     * 
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function beginTransaction()
    {
        return $this->driver->beginTransaction();
    }

    /**
     * Commits a transaction, returning the database connection to autocommit mode until the next 
     * call to EntityManager::beginTransaction() starts a new transaction.
     * 
     * @return bool TRUE on success or FALSE on failure.
     */
    public function commit()
    {
        return $this->driver->commit();
    }

    /**
     * Rolls back the current transaction, as initiated by EntityManager::beginTransaction().
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
        return $this->driver->rollBack();
    }

}
