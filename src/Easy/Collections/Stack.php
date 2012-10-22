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
 * Represents a simple last-in-first-out (LIFO) non-generic collection of objects.
 */
class Stack extends CollectionBase
{

    /**
     * Inserts an object at the top of the Stack.
     * @param type $item The Object to push onto the Stack. The value <b>can</b> be null.
     */
    public function push($item)
    {
        array_push($this->array, $item);
    }

    /**
     * Inserts multiples objects at the top of the Stack.
     * @param type $item The Objects to push onto the Stack. The value <b>can</b> be null.
     */
    public function pushMultiple($items)
    {
        $this->addMultiple($items);
    }

    /**
     * Removes and returns the object at the top of the Stack.
     * @return mixed The Object removed from the top of the Stack.
     * @throws BadFunctionCallException
     */
    public function pop()
    {
        if ($this->IsEmpty()) {
            throw new BadFunctionCallException(__('Cannot use method Pop on an empty Stack'));
        }
        return array_pop($this->array);
    }

    /**
     * Returns the object at the top of the Stack without removing it.
     * @return mixed The Object at the top of the Stack.
     * @throws BadFunctionCallException
     */
    public function peek()
    {
        if ($this->IsEmpty()) {
            throw new BadFunctionCallException(__('Cannot use method Peek on an empty Stack'));
        }

        return end($this->array);
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