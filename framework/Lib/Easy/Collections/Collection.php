<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 1.5.4
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('ICollection', "Collections");

/**
 * Provides the abstract base class for a strongly typed collection.
 * 
 * @package Easy.Collections
 */
abstract class Collection extends Object implements ICollection {

    /**
     * The objects Array
     * @var ArrayObject 
     */
    protected $data;

    public function __construct() {
        $this->data = new ArrayObject();
    }

    /**
     * Gets an element from the collection
     * @param mixed $i
     * @return mixed 
     */
    public function get($i) {
        return $this->data[$i];
    }

    /**
     * Adds a key/value object to the collection
     * @param mixed $key
     * @param mixed $object 
     */
    public function add($key, $object) {
        $this->data[$key] = $object;
    }

    /**
     * Appends the value to the collection
     * @param type $object 
     */
    public function append($object) {
        $this->data->append($object);
    }

    /**
     * Adds many values at once in the collection
     * @param array $values 
     */
    public function addRange(Array $values) {
        foreach ($values as $key => $value) {
            $this->add($key, $value);
        }
    }

    /**
     * Get how many elements the collection have
     * @return int 
     */
    public function count() {
        return $this->data->count();
    }

    /**
     * Removes one element from the collection
     * @param mixed $key 
     */
    public function remove($key) {
        unset($this->data[$key]);
    }

}
