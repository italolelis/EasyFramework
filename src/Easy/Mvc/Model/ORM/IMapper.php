<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\ORM;

interface IMapper
{

    public function getTableName();

    public function hasOne();

    public function hasMany();

    public function belongsTo();

    public function hasAndBelongsToMany();
}
