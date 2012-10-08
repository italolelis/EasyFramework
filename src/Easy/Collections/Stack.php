<?php

/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-03-06
 * @version 1.0 2009-03-07
 */

namespace Easy\Collections;

use Easy\Collections\BaseCollection;

class Stack extends BaseCollection
{

    public function Push($item)
    {
        array_push($this->array, $item);
    }

    public function Pop()
    {
        if ($this->IsEmpty()) {
            throw new BadFunctionCallException(__('Cannot use method Pop on an empty Stack'));
            return null;
        }
        return array_pop($this->array);
    }

    public function Peek()
    {
        if ($this->IsEmpty()) {
            throw new BadFunctionCallException(__('Cannot use method Peek on an empty Stack'));
            return null;
        }

        return end($this->array);
    }

    public function PushMultiple($items)
    {
        $this->addMultiple($items);
    }

    public function getIterator()
    {
        return new ArrayIterator(array_reverse($this->array));
    }

}