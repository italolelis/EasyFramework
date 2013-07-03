<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security\Hash;

use Easy\Security\HashInterface;

/**
 * Sha256 hashing class
 * 
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Sha256 implements HashInterface
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