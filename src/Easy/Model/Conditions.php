<?php

namespace Easy\Model;

use Easy\Collections\Collection;
use Easy\Model\Parser\ValueParser;

class Conditions extends Collection
{

    private $values;
    private $keys;

    public function __construct($array = null)
    {
        $condtitionParser = new ValueParser($array);
        $this->values = $condtitionParser->values();
        $this->keys = $condtitionParser->conditions();

        parent::__construct($array);
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setValues($values)
    {
        $this->values = $values;
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
