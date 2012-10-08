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

use Easy\Core\Object;

class Mapper extends Object implements IMapper
{

    protected $table;

    public function getTable()
    {
        return $this->table;
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

