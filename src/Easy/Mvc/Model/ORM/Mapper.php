<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\ORM;

/**
 * This class is responsible for mapping an entity with the database
 *
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Mapper implements IMapper
{

    protected $tableName;

    public function getTableName()
    {
        return $this->tableName;
    }

    public function hasAndBelongsToMany()
    {
        return null;
    }

    public function belongsTo()
    {
        return null;
    }

    public function hasMany()
    {
        return null;
    }

    public function hasOne()
    {
        return null;
    }

}

