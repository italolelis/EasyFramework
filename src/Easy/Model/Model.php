<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 0.2
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Model;

use Easy\Core\Object;
use Easy\Event\EventListener;
use Easy\Model\Relations\Relation;
use Easy\Serializer\JsonEncoder;

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
abstract class Model extends Object implements IModel
{

    public function __isset($name)
    {
        $relation = new Relation($this);
        return $relation->buildRelations($name);
    }

    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }

    public function toJSON()
    {
        return JsonEncoder::encode($this);
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete($cascade = true)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        return null;
    }

}

