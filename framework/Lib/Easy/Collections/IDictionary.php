<?php

/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-03-01
 * @version 1.0 2009-03-01
 */
interface IDictionary extends ArrayAccess, Countable, IteratorAggregate
{

    public function Add($key, $value);

    public function ContainsKey($key);

    public function ContainsValue($value);

    public function Clear();

    public function Remove($key);

    public function PrintCollection($UseVarDump = false);

    public function GetArray();

    public function Keys();

    public function Values();

    public function TryGetValue($key, &$value);
}