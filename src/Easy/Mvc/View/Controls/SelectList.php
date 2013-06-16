<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Controls;

use Easy\Collections\Collection;
use Easy\Collections\Dictionary;
use Easy\Mvc\View\Controls\SelectItem;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Represents a list that lets users select one item.
 */
class SelectList
{

    /**
     * The Collection of arrays or objects to handle
     * @var Dictionary <Object>
     */
    private $list;

    /**
     * @var Collection <SelectItem> The collection of SelectItem
     */
    private $items = array();

    /**
     * The field that's gonna be the value on the SelectItem
     * @var string 
     */
    private $value;

    /**
     * The field that's gonna be the text on the SelectItem
     * @var string 
     */
    private $display;

    /**
     * Initializes a new instance of the SelectList class by using the specified items for the list.
     * @param array|Collection $list
     * @param string $value The field's name that will be used in the value attribute
     * @param string $display The field's name that will be used in the display attribute
     */
    public function __construct($list, $value = null, $display = null)
    {
        $this->list = new Dictionary();

        foreach ($list as $key => $val) {
            $this->list->add($key, $val);
        }

        $this->value = $value;
        $this->display = $display;
    }

    /**
     * Gets the items in the list.
     * @return Collection
     */
    public function getItems()
    {
        if (empty($this->items)) {
            $this->items = new Collection();
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($this->list as $item => $value) {
                if (is_object($value)) {
                    $this->items->add(new SelectItem($accessor->getValue($value, $this->display), $accessor->getValue($value, $this->value)));
                } elseif ((bool) count(array_filter(array_keys($this->list->GetArray()), 'is_string'))) {
                    $this->items->add(new SelectItem($value, $item));
                } else {
                    $this->items->add(new SelectItem($value, $value));
                }
            }
        }
        return $this->items;
    }

    /**
     * Sets the items in the list.
     * @param Collection $items
     */
    public function setItems($items)
    {
        $this->list = $items;
    }

    /**
     * Gets the value fild's name
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value fild's name
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Gets the display fild's name
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Sets the display fild's name
     * @param string $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;
    }

}