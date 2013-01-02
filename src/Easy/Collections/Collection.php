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

use Easy\Collections\CollectionBase;

class Collection extends CollectionBase implements IList
{

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if (!is_numeric($offset)) {
            throw new InvalidArgumentException('The offset value must be numeric');
        }
        if ($offset < 0) {
            throw new InvalidArgumentException('The option value must be a number > 0');
        }
        return array_key_exists((int) $offset, $this->array);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->elementAt($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (!is_numeric($offset)) {
            throw new InvalidArgumentException('The offset value must be numeric');
        }
        if ($offset < 0) {
            throw new InvalidArgumentException('The option value must be a number > 0');
        }
        $this->array[(int) $offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->removeAt($offset);
    }

    /**
     * @inheritdoc
     */
    public function add($item)
    {
        array_push($this->array, $item);
    }

    /**
     * @inheritdoc
     */
    public function addRange($items)
    {
        $this->addMultiple($items);
    }

    /**
     * @inheritdoc
     */
    public function IndexOf($item, $start = null, $length = null)
    {
        return $this->getIndexOf($item, false, $start, $length);
    }

    /**
     * @inheritdoc
     */
    public function LastIndexOf($item, $start = null, $length = null)
    {
        return $this->getIndexOf($item, true, $start, $length);
    }

    /**
     * @inheritdoc
     */
    public function Insert($index, $item)
    {
        if (!is_numeric($index)) {
            throw new InvalidArgumentException('The index must be numeric');
        }
        if ($index < 0 || $index >= $this->Count()) {
            throw new InvalidArgumentException('The index is out of bounds (must be >=0 and <= size of te array)');
        }

        $current = $this->Count() - 1;
        for (; $current >= $index; $current--) {
            $this->array[$current + 1] = $this->array[$current];
        }
        $this->array[$index] = $item;
    }

    /**
     * @inheritdoc
     */
    public function remove($item)
    {
        if ($this->contains($item)) {
            $this->removeAt($this->getFirstIndex($item, $this->array));
        } else {
            throw new InvalidArgumentException('Item not found in the collection: <pre>' . var_export($item, true) . '</pre>');
        }
    }

    /**
     * @inheritdoc
     */
    public function removeAt($index)
    {
        if (!is_numeric($index)) {
            throw new InvalidArgumentException('The position must be numeric');
        }
        if ($index < 0 || $index >= $this->Count()) {
            throw new InvalidArgumentException('The index is out of bounds (must be >=0 and <= size of te array)');
        }

        $max = $this->Count() - 1;
        for (; $index < $max; $index++) {
            $this->array[$index] = $this->array[$index + 1];
        }
        array_pop($this->array);
    }

    /**
     * @inheritdoc
     */
    public function allIndexesOf($item)
    {
        return $this->getAllIndexes($item, $this->array);
    }

    /**
     * @inheritdoc
     */
    public function elementAt($index)
    {
        if ($this->offsetExists($index) === false) {
            throw new OutOfRangeException('No element at position ' . $index);
        }
        return $this->array[$index];
    }

    protected function getIndexOf($item, $lastIndex = false, $start = null, $length = null)
    {
        if ($start != null && !is_numeric($start)) {
            throw new InvalidArgumentException('The start value must be numeric or null');
            $start = null;
        }
        if ($length != null && !is_numeric($length)) {
            throw new InvalidArgumentException('The length value must be numeric or null');
            $length = null;
        }
        if ($start == null)
            $start = 0;
        if ($length == null)
            $length = count($this->array) - $start;
        $array = array_slice($this->array, $start, $length, true);

        if ($lastIndex == true)
            $array = array_reverse($array, true);
        $result = $this->getFirstIndex($item, $array);
        if ($result === false) {
            throw new InvalidArgumentException('Item not found in the collection: <pre>' . var_export($item, true) . '</pre>');
            return -1;
        }
        return $result;
    }

    protected function getAllIndexes($item, $array)
    {
        if (gettype($item) != 'object')
            $result = array_keys($array, $item, true);
        else {
            if ($item instanceof IEquatable) {
                $result = array();
                foreach ($array AS $k => $v) {
                    if ($item->Equals($v)) {
                        $result[] = $k;
                    }
                }
            } else {
                $result = array_keys($array, $item, false);
            }
        }
        if (!is_array($result))
            $result = array();
        return $result;
    }

    protected function getFirstIndex($item, $array)
    {
        $result = false;
        if (gettype($item) != 'object')
            $result = array_search($item, $array, true);
        else {
            if ($item instanceof IEquatable) {
                foreach ($array AS $k => $v) {
                    if ($item->Equals($v)) {
                        $result = $k;
                        break;
                    }
                }
            } else {
                $result = array_search($item, $array, false);
            }
        }
        return $result;
    }

}