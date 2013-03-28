<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Collections;

use Easy\Collections\Enumerable;

/**
 * Provides the abstract base class for a strongly typed collection.
 */
abstract class CollectionBase extends Enumerable implements CollectionInterface
{

    public function __construct($array = null)
    {
        if (is_array($array) || $array instanceof IteratorAggregate) {
            $this->addMultiple($array);
        }
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->GetArray());
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->array = array();
    }

    /**
     * @inheritdoc
     */
    public function contains($item)
    {
        return $this->itemExists($item, $this->array);
    }

    /**
     * @inheritdoc
     */
    public function IsEmpty()
    {
        return $this->count() < 1;
    }

    protected function addMultiple($items)
    {
        if (!is_array($items) && !($items instanceof IteratorAggregate)) {
            throw new InvalidArgumentException(__('Items must be either a Collection or an array'));
            return;
        }
        if ($items instanceof Enumerable) {
            $array = array_values($items->GetArray());
        } else if (is_array($items)) {
            $array = $items;
        } else if ($items instanceof IteratorAggregate) {
            foreach ($items as $k => $v) {
                $array[$k] = $v;
            }
        }
        if (empty($array) == false) {
            $this->array = array_merge($this->array, $array);
        }
    }

}