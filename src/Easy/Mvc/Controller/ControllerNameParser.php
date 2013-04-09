<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller;

/**
 * ControllerNameParser converts the namespace and get the controller name.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class ControllerNameParser
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