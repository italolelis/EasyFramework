<?php

/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-03-01
 * @version 1.0 2009-03-01
 */
interface ICollection extends ArrayAccess, Countable, IteratorAggregate
{

    public function Add($item);

    public function AddRange($items);

    public function Insert($index, $item);

    public function Contains($item);

    //public function Clear();

    public function IndexOf($item, $start = null, $length = null);

    public function LastIndexOf($item, $start = null, $length = null);

    public function AllIndexesOf($item);

    public function Remove($item);

    public function RemoveAt($index);

    public function ElementAt($index);

    //public function PrintCollection($UseVarDump = false);

    //public function GetArray();
}