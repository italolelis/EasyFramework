<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
