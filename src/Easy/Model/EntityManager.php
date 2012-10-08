<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 1.5
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Model;

use Easy\Collections\Collection;
use Easy\Core\App;
use Easy\Core\Object;
use Easy\Error\InvalidArgumentException;
use Easy\Event\EventManager;
use Easy\Model\ConnectionManager;
use Easy\Model\FindMethod;
use Easy\Model\Table;
use PDOException;

/**
 * Object-relational mapper.
 *
 * DBO-backed object data model.
 * Automatically selects a database table name based on a pluralized lowercase object class name
 * (i.e. class 'User' => table 'users'; class 'Man' => table 'men')
 * The table is required to have at least 'id auto_increment' primary key.
 *
 * @package Easy.Model
 */
class EntityManager extends Object
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
     * Container for the data that this model gets from persistent storage (usually, a database).
     *
     * @var array
     */
    public $data = array();

    /**
     * Table object.
     *
     * @var string
     */
    protected $table;

    /**
     * Connection Datasource object
     *
     * @var IDriver
     */
    protected $driver = false;

    /**
     *
     * @var Model 
     */
    protected $model;

    /**
     *
     * @var Model 
     */
    protected $mappers = array();

    /**
     * Instance of the EventManager this model is using
     * to dispatch inner events.
     *
     * @var EventManager
     */
    protected $_eventManager = null;

    public function __construct()
    {
        $this->driver = ConnectionManager::getDriver($this->useDbConfig);
    }

    /**
     * Returns the EventManager manager instance that is handling any callbacks.
     * You can use this instance to register any new listeners or callbacks to the
     * model events, or create your own events and trigger them at will.
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        if (empty($this->_eventManager)) {
            $this->_eventManager = new EventManager();
        }
        return $this->_eventManager;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;

        if (is_object($model)) {
            list(, $modelName) = namespaceSplit(get_class($model));
        } else {
            $modelName = $model;
        }

        //TODO: Implementar maneira de não instanciar mapper caso não seja necessário
        if (!isset($this->mappers[$modelName])) {
            $mapperClass = App::classname($modelName, "Model/Mapping", "Mapper");
            $this->mappers[$modelName] = new $mapperClass();
        }
        $config = $this->driver->getConfig();
        $this->table = new Table($this->driver, $this->mappers[$modelName]->getTable(), $config['prefix']);
    }

    public function getLastId()
    {
        return $this->driver->lastInsertedId();
    }

    public function getAffectedRows()
    {
        return $this->driver->getAffectedRows();
    }

    public function getDatasource()
    {
        return $this->driver;
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the current record's ID
     *
     * @param integer $list Index on which the composed ID is located
     * @return mixed The ID of the current record, false if no ID
     */
    public function getID($list = 0)
    {
        if (empty($this->id) || (is_array($this->id) && isset($this->id[0]) && empty($this->id[0]))) {
            return false;
        }

        if (!is_array($this->id)) {
            return $this->id;
        }

        if (isset($this->id[$list]) && !empty($this->id[$list])) {
            return $this->id[$list];
        } elseif (isset($this->id[$list])) {
            return false;
        }

        return current($this->id);
    }

    /**
     * Returns the contents of a single field given the supplied conditions, in the
     * supplied order.
     *
     * @param array $query SQL conditions (defaults to NULL)
     * @param EntityManager $type EntityManager constant, FIND_ALL or FIND_FIRST (defaults to FIND_FIRST)
     * @return string field contents, or false if not found
     */
    public function find($model, Query $query = null, $type = FindMethod::FIRST, $success = null, $error = null)
    {
        $this->setModel($model);

        if ($query === null) {
            $query = new Query();
        }

        if ($type === FindMethod::FIRST) {
            $this->data = $this->first($query, $success, $error);
        } elseif ($type === FindMethod::ALL) {
            $this->data = $this->all($query, $success, $error);
        }

        if (!$this->data instanceof Collection) {
            $this->data->afterFind();
        } else {
            foreach ($this->data as $result) {
                $result->afterFind();
            }
        }

        return $this->data;
    }

    /**
     * Handles the before/after filter logic for find('all') operations.  Only called by Model::find().
     *
     * @param array $params
     * @return array The result array
     * @see EntityManager::find()
     */
    protected function all(Query $query, $success = null, $error = null)
    {
        if (!$query->from()) {
            $query->from($this->getTable()->getName());
        }

        if (is_object($this->model)) {
            $results = $this->driver->read($query, get_class($this->model));
        } else {
            $class = App::className($this->model, "Model");
            $results = $this->driver->read($query, $class);
        }
        return new Collection($results);
    }

    /**
     * Handles the before/after filter logic for find('first') operations.  Only called by Model::find().
     *
     * @param array $params
     * @return array The result array
     * @see EntityManager::find()
     */
    protected function first(Query $query)
    {
        $query->limit(1);
        $results = $this->all($query);
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
        //$this->setModel($model);

        if ($query === null) {
            $query = new Query();
        }

        if (empty($fields)) {
            $query->select(array("COUNT(*) AS count"));
        } else {
            $query->select(array("COUNT(" . implode($fields) . ") AS count"), true);
        }

        return $this->find($model, $query)->count;
    }

    /**
     * Initializes the model for writing a new record, loading the default values
     * for those fields that are not defined in $data, and clearing previous validation errors.
     * Especially helpful for saving data in loops.
     *
     * @param mixed $data Data array to insert into the Database.
     * @return boolean True if the recorde was created, otherwise false
     */
    public function insert($data)
    {
        return $this->driver->create($this->getTable()->getName(), $data);
    }

    /**
     * Updates a model records based on a set of conditions.
     *
     * @param array $data Set of fields and values, indexed by fields.
     *    Fields are treated as SQL snippets, to insert literal values manually escape your data.
     * @param mixed $query Conditions to match, true for all records
     * @return boolean True on success, false on failure
     */
    public function update($data, Query $query)
    {
        return $this->driver->update($this->getTable()->getName(), $data, $query);
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

        $this->setModel($model);
        $model->beforeSave(); //Call the before save method

        $this->data = (array) $model;
        $this->data = array_intersect_key($this->data, $this->table->schema());

        $pk = $this->table->primaryKey();
        // verify if the record exists
        $exists = isset($model->{$pk}) && !empty($model->{$pk});
        $ok = true;

        if ($exists) {
            $query = new Query();
            $query->where(new Conditions(array($pk => $this->data[$pk])))
                    ->limit(1);
            $ok = (bool) $this->update($this->data, $query);
        } else {
            $ok = (bool) $this->insert($this->data);
        }

        if ($ok) {
            if (is_callable($success)) {
                return $success($model);
            }
        } else {
            if (is_callable($error)) {
                return $error($model);
            }
        }
    }

    /**
     * Removes record for given ID. If no ID is given, the current ID is used. Returns true on success.
     *
     * @param long $id ID of record to delete
     * @param boolean $cascade Set to true to delete records that depend on this record
     * @return boolean True on success
     */
    public function delete(IModel $model, $success = null, $error = null)
    {
        $this->setModel($model);
        //TODO: Implement cascade system
        $cascade = true;

        $query = new Query();
        $query->where(new Conditions(array($this->table->primaryKey() => $model->{$this->table->primaryKey()})))
                ->limit(1);

        $model->beforeDelete();
        if ($this->driver->delete($this->getTable()->getName(), $query)) {
            if (is_callable($success)) {
                $success($model);
            }
            return true;
        } else {
            if (is_callable($error)) {
                $error($model);
            }
            return false;
        }
    }

    /**
     * Returns true if a record with particular ID exists.
     *
     * If $id is not passed it calls Model::getID() to obtain the current record ID,
     * and then performs a Model::find('count') on the currently configured datasource
     * to ascertain the existence of the record in persistent storage.
     *
     * @param mixed $id ID of record to check for existence
     * @return boolean True if such a record exists
     */
    public function exists($id = null)
    {
        if ($id === null) {
            $id = $this->getID();
        }
        if ($id === false) {
            return false;
        }

        $conditions = array($this->primaryKey() => $id);
        $query = array('conditions' => $conditions);
        return ($this->count($query) > 0);
    }

    /**
     * Returns a list of fields from the database, and sets the current model
     * data (Model::$data) with the record found.
     *
     * @param mixed $fields String of single field name, or an array of field names.
     * @param mixed $id The ID of the record to read
     * @return array Array of database fields, or false if not found
     */
    public function read($model, $id, $fields = null, $beforeFind = null, $afterFind = null)
    {
        $this->setModel($model);
        if ($id != null) {
            $this->id = $id;
        }

        if ($fields === null) {
            $fields = array("*");
        }

        $id = $this->id;

        if (is_array($this->id)) {
            $id = $this->id[0];
        }

        $query = new Query();
        $query->select($fields)
                ->where(new Conditions(array($this->table->primaryKey() => $id)));

        if ($id !== null && $id !== false) {
            $this->data = $this->find($model, $query, FindMethod::FIRST, $beforeFind, $afterFind);
            return $this->data;
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
