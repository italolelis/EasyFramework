<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller;

class ControllerObjectNameParser
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