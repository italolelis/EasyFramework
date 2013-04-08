<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\ORM;

use Easy\Collections\Collection;
use Easy\Mvc\Model\ORM\Parser\ExpressionParser;

class Conditions extends Collection
{

    private $values;
    private $keys;

    public function __construct($array = null)
    {
        parent::__construct($array);
        if (is_array($array)) {
            $condtitionParser = new ExpressionParser($array);
            $this->values = $condtitionParser->values();
            $this->keys = $condtitionParser->conditions();
        } else {
            $this->values = array();
            $this->keys = $array;
        }
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setValues($values)
    {
        $this->values = $values;
    }

    public function addValues($values)
    {
        $this->values = array_merge_recursive($this->values, $values);
    }

    public function getKeys()
    {
        return $this->keys;
    }

    public function setKeys($keys)
    {
        $this->keys = $keys;
    }

}
