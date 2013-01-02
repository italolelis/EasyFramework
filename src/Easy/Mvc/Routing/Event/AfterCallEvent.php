<?php

namespace Easy\Mvc\Routing\Event;

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

    public function getController()
    {
        return $this->controller;
    }

}
