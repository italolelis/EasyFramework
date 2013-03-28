<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc\Controller\Component;

use Easy\Mvc\Controller\ControllerAware;
use Easy\Storage;

/**
 * Cookie handling for controller.
 * 
 * @since 1.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Cookie extends ControllerAware
{

    private $cookie;

    public function __construct()
    {
        $this->cookie = new Storage\Cookie();
    }

    public function delete($name)
    {
        return Storage\Cookie::retrieve($name)->delete();
    }

    public function read($name)
    {
        return Storage\Cookie::retrieve($name)->get();
    }

    public function write($name, $value, $expires = Storage\Cookie::SESSION)
    {
        if ($expires === null) {
            $expires = Storage\Cookie::SESSION;
        }
        $this->cookie->setName($name);
        $this->cookie->setValue($value);
        $this->cookie->setTime($expires);
        return $this;
    }

    public function create()
    {
        return $this->cookie->create();
    }

}
