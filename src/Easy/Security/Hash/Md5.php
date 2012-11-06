<?php

namespace Easy\Security\Hash;

use Easy\Security\IHash;

/**
 * Bcrypt hashing class
 * 
 * @author Thiago Belem <contato@thiagobelem.net>
 * @link   https://gist.github.com/3438461
 */
class Md5 extends Hash implements IHash
{

    /**
     * Hash a string
     * 
     * @param  string  $string The string
     * 
     * @see    http://www.php.net/manual/en/function.crypt.php
     * 
     * @return string
     */
    public function hash($string)
    {
        if (function_exists('hash')) {
            return hash('md5', $string);
        } else {
            return md5($string);
        }
    }

    /**
     * Check a hashed string
     * 
     * @param  string $string The string
     * @param  string $hash   The hash
     * 
     * @return boolean
     */
    public function check($string, $hash)
    {
        return ($this->hash($string) === $hash);
    }

}