<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 * @since         EasyFramework v 1.5.4
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Defines methods to manipulate generic collections. 
 * 
 * @package Easy.Collections
 */
interface ICollection {

    /**
     * Gets the number of elements contained in the ICollection().
     */
    public function count();

    /**
     * Adds an item to the ICollection().
     */
    public function add($key, $value);

    public function append($object);

    public function addRange(Array $values);

    public function get($key);

    /**
     * Removes the first occurrence of a specific object from the ICollection().
     */
    public function remove($key);
}