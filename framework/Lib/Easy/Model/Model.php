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
App::uses('Event', 'Event');
App::uses('EventListener', 'Event');
App::uses('EventManager', 'Event');
App::uses('ModelState', 'Model');
App::uses('Relation', 'Model/Relations');

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
abstract class Model extends Object implements EventListener
{

    public $table;
    public $validate = array();

    /**
     * The ModelState of this model
     * @var ModelState 
     */
    protected $modelState;

    /**
     * The EntityManager for this model
     * @var EntityManager 
     */
    protected $entityManager;
    public $hasOne;
    public $hasMany;
    public $belongsTo;

    public function Model()
    {
        $this->entityManager = new EntityManager($this);
        $this->modelState = new ModelState($this);
    }

    public function __isset($name)
    {
        $relation = new Relation($this);
        return $relation->buildRelations();
    }

    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }

    public function getModelState()
    {
        return $this->modelState;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Saves model data (based on white-list, if supplied) to the database. By
     * default, validation occurs before save.
     *
     * @param array $data Data to save.
     * @return boolean On success true, false on failure
     */
    public function save($data)
    {
        return $this->entityManager->save($data);
    }

    /**
     * Removes record for given ID. If no ID is given, the current ID is used. Returns true on success.
     *
     * @param long $id ID of record to delete
     * @param boolean $cascade Set to true to delete records that depend on this record
     * @return boolean True on success
     */
    public function delete($id)
    {
        return $this->entityManager->delete($id);
    }

    /**
     *  Count the registers of a given condition
     *
     *  @param array $params SQL Conditions
     *  @return int The register's count
     */
    public function count($params = array())
    {
        return $this->entityManager->count($params);
    }

    /**
     * Returns a list of all events that will fire in the model during it's lifecycle.
     * You can override this function to add you own listener callbacks
     *
     * @return array
     */
    public function implementedEvents()
    {
        return array(
            'Model.beforeFind' => array('callable' => 'beforeFind', 'passParams' => true),
            'Model.afterFind' => array('callable' => 'afterFind', 'passParams' => true),
            'Model.beforeValidate' => array('callable' => 'beforeValidate', 'passParams' => true),
            'Model.beforeSave' => array('callable' => 'beforeSave', 'passParams' => true),
            'Model.afterSave' => array('callable' => 'afterSave', 'passParams' => true),
            'Model.beforeDelete' => array('callable' => 'beforeDelete', 'passParams' => true),
            'Model.afterDelete' => array('callable' => 'afterDelete'),
        );
    }

    /**
     * Called before each find operation. Return false if you want to halt the find
     * call, otherwise return the (modified) query data.
     *
     * @param array $queryData Data used to execute this query, i.e. conditions, order, etc.
     * @return mixed true if the operation should continue, false if it should abort; or, modified
     *               $queryData to continue with new $queryData
     */
    public function beforeFind($queryData)
    {
        return true;
    }

    /**
     * Called after each find operation. Can be used to modify any results returned by find().
     * Return value should be the (modified) results.
     *
     * @param mixed $results The results of the find operation
     * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
     * @return mixed Result of the find operation
     */
    public function afterFind($results, $primary = false)
    {
        return $results;
    }

    /**
     * Called before each save operation, after validation. Return a non-true result
     * to halt the save.
     *
     * @param array $options
     * @return boolean True if the operation should continue, false if it should abort
     */
    public function beforeSave($options = array())
    {
        return true;
    }

    /**
     * Called after each successful save operation.
     *
     * @param boolean $created True if this save created a new record
     * @return void
     */
    public function afterSave($created)
    {
        
    }

    /**
     * Called before every deletion operation.
     *
     * @param boolean $cascade If true records that depend on this record will also be deleted
     * @return boolean True if the operation should continue, false if it should abort
     */
    public function beforeDelete($cascade = true)
    {
        return true;
    }

    /**
     * Called after every deletion operation.
     *
     * @return void
     */
    public function afterDelete()
    {
        
    }

    /**
     * Called during validation operations, before validation. Please note that custom
     * validation rules can be defined in $validate.
     *
     * @param array $options Options passed from model::save(), see $options of model::save().
     * @return boolean True if validate operation should continue, false to abort
     */
    public function beforeValidate($options = array())
    {
        return true;
    }

}

