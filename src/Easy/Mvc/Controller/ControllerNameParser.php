<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc\Controller;

use Easy\Core\Object;

/**
 * ControllerNameParser converts the namespace and get the controller name.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class ControllerNameParser extends Object
{

    protected $name;
    protected $namespace;

    public function __construct($controller)
    {
        list($this->namespace, $name) = namespaceSplit(get_class($controller));
        $this->name = substr($name, 0, -10);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

}