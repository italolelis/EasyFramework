<?php

/**
 * Core Security
 *
 * PHP 5
 *
 * FROM CAKEPHP
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Utility
 * @since         CakePHP(tm) v .0.10.0.1233
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Security;

use Easy\Core\App;
use Easy\Core\Config;
use Easy\Utility\String;
use Easy\Utility\Inflector;

/**
 * Security Library contains utility methods related to security
 *
 * @package Cake.Utility
 */
class Security
{

    /**
     * Default hash method
     *
     * @var string
     */
    protected static $hashType = null;

    /**
     * Engine instances keyed by configuration name.
     *
     * @var array
     */
    protected static $_engines = array();

    /**
     * Get allowed minutes of inactivity based on security level.
     *
     * @return integer Allowed inactivity in minutes
     */
    public static function inactiveMins()
    {
        switch (Config::read('Security.level')) {
            case 'high' :
                return 10;
                break;
            case 'medium' :
                return 100;
                break;
            case 'low' :
            default :
                return 300;
                break;
        }
    }

    /**
     * Generate authorization hash.
     *
     * @return string Hash
     */
    public static function generateAuthKey()
    {
        return Security::hash(String::uuid());
    }

    /**
     * Validate authorization hash.
     *
     * @param $authKey string Authorization hash
     * @return boolean Success
     * @todo Complete implementation
     */
    public static function validateAuthKey($authKey)
    {
        return true;
    }

    /**
     * Create a hash from string using given method.
     * Fallback on next available method.
     *
     * @param $string string String to hash
     * @param $type string Method to use (sha1/sha256/md5)
     * @param $salt boolean If true, automatically appends the application's salt
     *        value to $string (Security.salt)
     * @return string Hash
     */
    public static function hash($string, $type = null, $salt = false)
    {
        $engine = static::getEngine($type);
        return $engine->hash($string);
    }

    public static function check($string, $hash, $type = null)
    {
        $engine = static::getEngine($type);
        return $engine->check($string, $hash);
    }

    protected static function getEngine($type = null)
    {
        if ($type === null) {
            $type = Inflector::camelize(Config::read("Security.hash"));
            $options = array();
            if (is_array($type)) {
                $options = array_values($type);
                $type = key($type);
            }
        }

        if (!isset(static::$_engines[$type])) {
            $className = App::classname($type, "Security/Hash");
            static::$_engines[$type] = new $className($options);
        }

        return static::$_engines[$type];
    }

    /**
     * Sets the default hash method for the Security object.
     * This affects all objects using
     * Security::hash().
     *
     * @param $hash string Method to use (sha1/sha256/md5)
     * @return void
     * @see Security::hash()
     */
    public static function setHash($hash)
    {
        self::$hashType = $hash;
    }

    /**
     *
     * @return the $hashType
     */
    public static function getHashType()
    {
        return Security::$hashType;
    }

    /**
     * Encrypts/Decrypts a text using the given key.
     *
     * @param $text string Encrypted string to decrypt, normal string to encrypt
     * @param $key string Key to use
     * @return string Encrypted/Decrypted string
     */
    public static function cipher($text, $key)
    {
        if (empty($key)) {
            trigger_error('You cannot use an empty key for Security::cipher()', E_USER_WARNING);
            return '';
        }

        srand(Config::read('Security.cipherSeed'));
        $out = '';
        $keyLength = strlen($key);
        for ($i = 0, $textLength = strlen($text); $i < $textLength; $i++) {
            $j = ord(substr($key, $i % $keyLength, 1));
            while ($j--) {
                rand(0, 255);
            }
            $mask = rand(0, 255);
            $out .= chr(ord(substr($text, $i, 1)) ^ $mask);
        }
        srand();
        return $out;
    }

    /**
     * Encrypts/Decrypts a text using the given key using rijndael method.
     *
     * @param string $text Encrypted string to decrypt, normal string to encrypt
     * @param string $key Key to use
     * @param string $operation Operation to perform, encrypt or decrypt
     * @return string Encrypted/Descrypted string
     */
    public static function rijndael($text, $key, $operation)
    {
        if (empty($key)) {
            trigger_error(__('You cannot use an empty key for Security::rijndael()'), E_USER_WARNING);
            return '';
        }
        if (empty($operation) || !in_array($operation, array('encrypt', 'decrypt'))) {
            trigger_error(__('You must specify the operation for Security::rijndael(), either encrypt or decrypt'), E_USER_WARNING);
            return '';
        }
        if (strlen($key) < 32) {
            trigger_error(__('You must use a key larger than 32 bytes for Security::rijndael()'), E_USER_WARNING);
            return '';
        }
        $algorithm = 'rijndael-256';
        $mode = 'cbc';
        $cryptKey = substr($key, 0, 32);
        $iv = substr($key, strlen($key) - 32, 32);
        $out = '';
        if ($operation === 'encrypt') {
            $out .= mcrypt_encrypt($algorithm, $cryptKey, $text, $mode, $iv);
        } elseif ($operation === 'decrypt') {
            $out .= rtrim(mcrypt_decrypt($algorithm, $cryptKey, $text, $mode, $iv), "\0");
        }
        return $out;
    }

}
