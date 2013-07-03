<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Storage;

use DateTime;

/**
 *  Cookie cuida da criação e leitura de cookies para o EasyFramework, levando em conta
 *  aspectos de segurança, encriptando todos os cookies criados.
 */
class Cookie
{

    const SESSION = null;
    const ONE_DAY = "+1 day";
    const SEVEN_DAYS = "+7 days";
    const THIRTY_DAYS = "+1 month";
    const SIX_MONTHS = "+6 months";
    const ONE_YEAR = "+1 year";
    const LIFETIME = -1; // 2030-01-01 00:00:00

    /**
     * Cookie name - the name of the cookie.
     * @var bool
     */

    private $name = false;

    /**
     * Cookie value
     * @var string
     */
    private $value = "";

    /**
     * Cookie life time
     * @var DateTime
     */
    private $time;

    /**
     * Cookie domain
     * @var bool
     */
    private $domain = false;

    /**
     * Cookie path
     * @var bool
     */
    private $path = false;

    /**
     * Cookie secure
     * @var bool
     */
    private $secure = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        
    }

    /**
     * Create or Update cookie.
     */
    public function create()
    {
        return setcookie($this->name, urlencode(serialize($this->getValue())), $this->getTime(), $this->getPath(), $this->getDomain(), $this->getSecure(), true);
    }

    /**
     * Return a cookie
     * @return mixed
     */
    public function get()
    {
        if (isset($_COOKIE[$this->getName()])) {
            return unserialize(urldecode($_COOKIE[$this->getName()]));
        }
        return null;
    }

    /**
     * Delete cookie.
     * @return bool
     */
    public function delete()
    {
        return setcookie($this->name, '', time() - 3600, $this->getPath(), $this->getDomain(), $this->getSecure(), true);
    }

    /**
     * Returns a cookie instace for the key
     * @param string $name
     * @return Cookie
     */
    public static function retrieve($name)
    {
        $cookie = new Cookie();
        $cookie->setName($name);
        return $cookie;
    }

    /**
     * @param $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return bool
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param $id
     */
    public function setName($id)
    {
        $this->name = $id;
    }

    /**
     * @return bool
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return bool
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @param $time
     */
    public function setTime($time)
    {
        // Create a date
        $date = new DateTime();
        // Modify it (+1hours; +1days; +20years; -2days etc)
        $date->modify($time);
        // Store the date in UNIX timestamp.
        $this->time = $date->getTimestamp();
    }

    /**
     * @return bool|int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}