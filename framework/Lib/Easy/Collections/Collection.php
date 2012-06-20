<?

/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-03-04
 * @version 1.5 2009-03-05
 */
App::uses('BaseCollection', 'Collections');
App::uses('ICollection', 'Collections');

class Collection extends BaseCollection implements ICollection
{

    public function __construct($array = null)
    {
        if (is_array($array) || $array instanceof IteratorAggregate)
            $this->AddRange($array);
    }

    public function offsetExists($offset)
    {
        if (!is_numeric($offset)) {
            throw new InvalidArgumentException('The offset value must be numeric');
            return false;
        }
        if ($offset < 0) {
            throw new InvalidArgumentException('The option value must be a number > 0');
            return false;
        }
        return array_key_exists((int) $offset, $this->array);
    }

    public function offsetGet($offset)
    {
        return $this->ElementAt($offset);
    }

    public function offsetSet($offset, $value)
    {
        if (!is_numeric($offset)) {
            throw new InvalidArgumentException('The offset value must be numeric');
            return;
        }
        if ($offset < 0) {
            throw new InvalidArgumentException('The option value must be a number > 0');
            return;
        }
        $this->array[(int) $offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->RemoveAt($offset);
    }

    public function Add($item)
    {
        array_push($this->array, $item);
    }

    public function AddRange($items)
    {
        $this->addMultiple($items);
    }

    public function Contains($item)
    {
        return $this->itemExists($item, $this->array);
    }

    public function IndexOf($item, $start = null, $length = null)
    {
        return $this->getIndexOf($item, false, $start, $length);
    }

    public function LastIndexOf($item, $start = null, $length = null)
    {
        return $this->getIndexOf($item, true, $start, $length);
    }

    public function Insert($index, $item)
    {
        if (!is_numeric($index)) {
            throw new InvalidArgumentException('The index must be numeric');
            return;
        }
        if ($index < 0 || $index >= $this->Count()) {
            throw new InvalidArgumentException('The index is out of bounds (must be >=0 and <= size of te array)');
            return;
        }

        $current = $this->Count() - 1;
        for (; $current >= $index; $current--) {
            $this->array[$current + 1] = $this->array[$current];
        }
        $this->array[$index] = $item;
    }

    public function Remove($item)
    {
        if ($this->Contains($item)) {
            $this->RemoveAt($this->getFirstIndex($item, $this->array));
        } else {
            throw new InvalidArgumentException('Item not found in the collection: <pre>' . var_export($item, true) . '</pre>');
        }
    }

    public function RemoveAt($index)
    {
        if (!is_numeric($index)) {
            throw new InvalidArgumentException('The position must be numeric');
            return;
        }
        if ($index < 0 || $index >= $this->Count()) {
            throw new InvalidArgumentException('The index is out of bounds (must be >=0 and <= size of te array)');
            return;
        }

        $max = $this->Count() - 1;
        for (; $index < $max; $index++) {
            $this->array[$index] = $this->array[$index + 1];
        }
        array_pop($this->array);
    }

    public function AllIndexesOf($item)
    {
        return $this->getAllIndexes($item, $this->array);
    }

    public function ElementAt($index)
    {
        if ($this->offsetExists($index) == false) {
            throw new OutOfRangeException('No element at position ' . $index);
            return null;
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