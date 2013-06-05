<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security\Hash;

use Easy\Security\HashInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Bcrypt algorithm using crypt() function of PHP
 */
class Bcrypt implements HashInterface
{

    const MIN_SALT_SIZE = 16;

    /**
     * @var string
     */
    protected $cost = '14';

    /**
     * @var string
     */
    protected $salt;

    /**
     * @var bool
     */
    protected $backwardCompatibility = false;

    /**
     * Constructor
     *
     * @param array $options
     * @throws InvalidArgumentException
     */
    public function __construct($options = array())
    {
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                switch (strtolower($key)) {
                    case 'salt':
                        $this->setSalt($value);
                        break;
                    case 'cost':
                        $this->setCost($value);
                        break;
                }
            }
        }
    }

    /**
     * Bcrypt
     *
     * @param  string $string
     * @throws RuntimeException
     * @return string
     */
    public function hash($string)
    {
        if (empty($this->salt)) {
            $salt = Rand::getBytes(self::MIN_SALT_SIZE);
        } else {
            $salt = $this->salt;
        }
        $salt64 = substr(str_replace('+', '.', base64_encode($salt)), 0, 22);
        /**
         * Check for security flaw in the bcrypt implementation used by crypt()
         * @see http://php.net/security/crypt_blowfish.php
         */
        if ((version_compare(PHP_VERSION, '5.3.7') >= 0) && !$this->backwardCompatibility) {
            $prefix = '$2y$';
        } else {
            $prefix = '$2a$';
            // check if the password contains 8-bit character
            if (preg_match('/[\x80-\xFF]/', $string)) {
                throw new RuntimeException(
                'The bcrypt implementation used by PHP can contains a security flaw ' .
                'using password with 8-bit character. ' .
                'We suggest to upgrade to PHP 5.3.7+ or use passwords with only 7-bit characters'
                );
            }
        }
        $hash = crypt($string, $prefix . $this->cost . '$' . $salt64);
        if (strlen($hash) < 13) {
            throw new RuntimeException('Error during the bcrypt generation');
        }
        return $hash;
    }

    /**
     * Verify if a password is correct against an hash value
     *
     * @param  string $string
     * @param  string $hash
     * @return bool
     */
    public function check($string, $hash)
    {
        return ($hash === crypt($string, $hash));
    }

    /**
     * Set the cost parameter
     *
     * @param  int|string $cost
     * @throws InvalidArgumentException
     * @return Bcrypt
     */
    public function setCost($cost)
    {
        if (!empty($cost)) {
            $cost = (int) $cost;
            if ($cost < 4 || $cost > 31) {
                throw new InvalidArgumentException(
                'The cost parameter of bcrypt must be in range 04-31'
                );
            }
            $this->cost = sprintf('%1$02d', $cost);
        }
        return $this;
    }

    /**
     * Get the cost parameter
     *
     * @return string
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set the salt value
     *
     * @param  string $salt
     * @throws InvalidArgumentException
     * @return Bcrypt
     */
    public function setSalt($salt)
    {
        if (strlen($salt) < self::MIN_SALT_SIZE) {
            throw new InvalidArgumentException(
            'The length of the salt must be at least ' . self::MIN_SALT_SIZE . ' bytes'
            );
        }
        $this->salt = $salt;
        return $this;
    }

    /**
     * Get the salt value
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set the backward compatibility $2a$ instead of $2y$ for PHP 5.3.7+
     *
     * @param bool $value
     * @return Bcrypt
     */
    public function setBackwardCompatibility($value)
    {
        $this->backwardCompatibility = (bool) $value;
        return $this;
    }

    /**
     * Get the backward compatibility
     *
     * @return bool
     */
    public function getBackwardCompatibility()
    {
        return $this->backwardCompatibility;
    }

}