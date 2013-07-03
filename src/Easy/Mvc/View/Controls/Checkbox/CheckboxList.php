<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Controls\Checkbox;

use Easy\Collections\Dictionary;
use Easy\Collections\Collection;

class CheckboxList
{

    /**
     * The Collection of arrays or objects to handle
     * @var Dictionary <Object>
     */
    private $list;

    /**
     * @var Collection <CheckboxItem>
     */
    private $items = array();

    /**
     * The field that's gonna be the value on the CheckboxItem
     * @var string 
     */
    private $value;

    /**
     * The field that's gonna be the text on the CheckboxItem
     * @var string 
     */
    private $display;

    public function __construct($list, $value, $display)
    {
        $this->list = new Dictionary();

        foreach ($list as $key => $val) {
            $this->list->add($key, $val);
        }

        $this->value = $value;
        $this->display = $display;
    }

    public function getItems()
    {
        if (empty($this->items)) {
            $this->items = new Collection();
            foreach ($this->list as $item => $value) {
                if (is_object($value)) {
                    $this->items->add(new CheckboxItem($value->{$this->display}, $value->{$this->value}));
                } else {
                    $this->items->add(new CheckboxItem($value, $item));
                }
            }
        }
        return $this->items;
    }

    public function setItems($items)
    {
        $this->list = $items;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function setDisplay($display)
    {
        $this->display = $display;
    }

}