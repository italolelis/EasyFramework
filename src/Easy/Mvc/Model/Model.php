<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model;

use Easy\Core\Object;
use Easy\Mvc\Model\ORM\Relations\Relation;

/**
 * Object-relational mapper.
 *
 * DBO-backed object data model.
 * Automatically selects a database table name based on a pluralized lowercase object class name
 * (i.e. class 'User' => table 'users'; class 'Man' => table 'men')
 * The table is required to have at least 'id auto_increment' primary key.
 * 
 * @since 0.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
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

