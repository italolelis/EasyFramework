<?php

App::uses('Enumerable', 'Collections');
App::uses('IDictionary', 'Collections');

/**
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-03-04
 * @version 1.5 2009-03-05
 */
class Dictionary extends Enumerable implements IDictionary
{

    public function offsetExists($offset)
    {
        return $this->ContainsKey($offset);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset) == false) {
            throw new InvalidArgumentException(__('The key is not present in the dictionary'));
            return null;
        }
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->Add($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->Remove($offset);
    }

    public function Add($key, $value)
    {
        if ($key === null) {
            throw new InvalidArgumentException(__("Can't use 'null' as key!"));
        }
        if ($this->ContainsKey($key)) {
            throw new InvalidArgumentException(__('That key already exists!'));
            return;
        }
        $this->array[$key] = $value;
    }

    public function ContainsKey($key)
    {
        return $this->itemExists($key, $this->Keys());
    }

    public function ContainsValue($value)
    {
        return $this->itemExists($value, $this->Values());
    }

    public function Remove($key)
    {
        if ($this->ContainsKey($key) == false) {
            throw new InvalidArgumentException(__('The key is not present in the dictionary'));
            return;
        }
        unset($this->array[$key]);
    }

    public function Keys()
    {
        return array_keys($this->array);
    }

    public function Values()
    {
        return array_values($this->array);
    }

    public function TryGetValue($key, &$value)
    {
        if ($this->ContainsKey($key)) {
            $value = $this[$key];
            return true;
        } else {
            $value = null;
            return false;
        }
    }

}