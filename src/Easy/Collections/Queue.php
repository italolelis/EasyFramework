<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Collections;

use BadFunctionCallException;
use Easy\Collections\CollectionBase;

/**
 * Represents a first-in, first-out collection of objects.
 */
class Queue extends CollectionBase
{

    /**
     * Adds an object to the end of the Queue.
     * @param mixed $item The object to add to the Queue. The value can be null.
     */
    public function enqueue($item)
    {
        array_push($this->array, $item);
    }

    /**
     * Adds multiples objects to the end of the Queue.
     * @param ICollection|array $items The objects to add to the Queue. The value can be null.
     */
    public function enqueueMultiple($items)
    {
        $this->addMultiple($items);
    }

    /**
     * Removes and returns the object at the beginning of the Queue.
     * @return mixed The object that is removed from the beginning of the Queue.
     * @throws BadFunctionCallException
     */
    public function dequeue()
    {
        if ($this->IsEmpty()) {
            throw new BadFunctionCallException(_('Cannot use method Dequeue on an empty Queue'));
        }
        return array_shift($this->array);
    }

    /**
     * Returns the object at the beginning of the Queue without removing it.
     * @return mixed The object at the beginning of the Queue.
     * @throws BadFunctionCallException
     */
    public function peek()
    {
        if ($this->IsEmpty()) {
            throw new BadFunctionCallException(__('Cannot use method Peek on an empty Queue'));
        }

        return $this->array[0];
    }

    public function offsetExists($offset)
    {
        
    }

    public function offsetGet($offset)
    {
        
    }

    public function offsetSet($offset, $value)
    {
        
    }

    public function offsetUnset($offset)
    {
        
    }

}