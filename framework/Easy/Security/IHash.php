<?php

namespace Easy\Security;

/**
 *
 * @author italo
 */
interface IHash
{

    public function hash($string);

    public function check($string, $hash);
}
