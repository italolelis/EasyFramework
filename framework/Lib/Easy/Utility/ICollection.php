<?php

interface ICollection {

    public function count();

    public function add($key, $value);

    public function addRange($values);

    public function get($key);

    public function remove($key);
}

?>