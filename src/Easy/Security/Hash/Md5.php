<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security\Hash;

use Easy\Security\HashInterface;

/**
 * Md5 hashing class
 * 
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Md5 implements HashInterface
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