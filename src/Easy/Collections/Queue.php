<?php

/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-03-06
 * @version 1.0 2009-03-07
 */

namespace Easy\Collections;

use Easy\Collections\BaseCollection;

class Queue extends BaseCollection implements \Countable
{

    public function Enqueue($item)
    {
        array_push($this->array, $item);
    }

    public function EnqueueMultiple($items)
    {
        $this->addMultiple($items);
    }

    public function Dequeue()
    {
        if ($this->IsEmpty()) {
            throw new BadFunctionCallException(_('Cannot use method Dequeue on an empty Queue'));
            return null;
        }
        return array_shift($this->array);
    }

    public function Peek()
    {
        if ($this->IsEmpty()) {
            throw new BadFunctionCallException(__('Cannot use method Peek on an empty Queue'));
            return null;
        }

        return $this->array[0];
    }

    public function count()
    {
        return count($this->array);
    }

}