<?php

namespace Easy\Security;

/**
 * Represents the hash interface
 */
interface IHash
{

    public function hash($string);

    public function check($string, $hash);
}
