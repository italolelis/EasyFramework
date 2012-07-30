<?php

/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-03-03
 * @version 1.0 2009-03-04
 */
App::uses('Enumerable', 'Collections');

abstract class BaseCollection extends Enumerable
{

    public function Contains($item)
    {
        return $this->itemExists($item, $this->array);
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
            $array = array_values($items);
        } else if ($items instanceof IteratorAggregate) {
            foreach ($items AS $v) {
                $array[] = $v;
            }
        }
        if (empty($array) == false) {
            $this->array = array_merge($this->array, $array);
        }
    }

}