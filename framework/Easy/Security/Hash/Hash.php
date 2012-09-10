<?php

namespace Easy\Security\Hash;

/**
 * Bcrypt hashing class
 * 
 * @author Thiago Belem <contato@thiagobelem.net>
 * @link   https://gist.github.com/3438461
 */
abstract class Hash
{

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

}