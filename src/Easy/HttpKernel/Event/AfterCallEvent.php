<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\Event;

use Easy\Mvc\Controller\Controller;
use Symfony\Component\EventDispatcher\Event;

class AfterCallEvent extends Event
{

    protected $controller;
    protected $result;

    public function __construct(Controller $controller, $result)
    {
        $this->controller = $controller;
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    public function getController()
    {
        return $this->controller;
    }

}
