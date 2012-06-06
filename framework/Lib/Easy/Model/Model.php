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
abstract class Model extends Object {

    const FIND_FIRST = 'first';
    const FIND_ALL = 'all';

    public $table;
    public $validate = array();

    /**
     *
     * @var ModelState 
     */
    protected $modelState;

    /**
     * The EntityManager for this model
     * @var EntityManager 
     */
    protected $entityManager;

    public function Model() {
        $this->entityManager = new EntityManager($this);
        $this->modelState = new ModelState($this);
    }

    public function getModelState() {
        return $this->modelState;
    }

    public function getEntityManager() {
        return $this->entityManager;
    }

    /**
     * Saves model data (based on white-list, if supplied) to the database. By
     * default, validation occurs before save.
     *
     * @param array $data Data to save.
     * @return boolean On success true, false on failure
     */
    public function save($data) {
        return $this->entityManager->save($data);
    }

    /**
     * Removes record for given ID. If no ID is given, the current ID is used. Returns true on success.
     *
     * @param long $id ID of record to delete
     * @param boolean $cascade Set to true to delete records that depend on this record
     * @return boolean True on success
     */
    public function delete($id) {
        return $this->entityManager->delete($id);
    }

    /**
     *  Count the registers of a given condition
     *
     *  @param array $params SQL Conditions
     *  @return int The register's count
     */
    public function count($params = array()) {
        return $this->entityManager->count($params);
    }

}

