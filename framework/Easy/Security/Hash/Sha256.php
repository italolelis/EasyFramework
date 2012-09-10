<?php

namespace Easy\Security\Hash;

use Easy\Security\IHash;

/**
 * Bcrypt hashing class
 * 
 * @author Thiago Belem <contato@thiagobelem.net>
 * @link   https://gist.github.com/3438461
 */
class Sha256 extends Hash implements IHash
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
    public static function hash($string)
    {
        if (function_exists('hash')) {
            return hash('sha256', $string);
        } else {
            return bin2hex(mhash(MHASH_SHA256, $string));
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
    public static function check($string, $hash)
    {
        return (static::hash($string) === $hash);
    }

}