<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Model;

use Easy\Serializer\IJson;

interface IModel extends IJson
{

    /**
     * Called after each find operation. Can be used to modify any results returned by find().
     * Return value should be the (modified) results.
     *
     * @param mixed $results The results of the find operation
     * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
     * @return mixed Result of the find operation
     */
    public function afterFind();

    /**
     * Called before each save operation, after validation. Return a non-true result
     * to halt the save.
     *
     * @param array $options
     * @return boolean True if the operation should continue, false if it should abort
     */
    public function beforeSave();

    /**
     * Called after each successful save operation.
     *
     * @param boolean $created True if this save created a new record
     * @return void
     */
    public function afterSave();

    /**
     * Called before every deletion operation.
     *
     * @param boolean $cascade If true records that depend on this record will also be deleted
     * @return boolean True if the operation should continue, false if it should abort
     */
    public function beforeDelete($cascade = true);

    /**
     * Called after every deletion operation.
     *
     * @return void
     */
    public function afterDelete();
}
