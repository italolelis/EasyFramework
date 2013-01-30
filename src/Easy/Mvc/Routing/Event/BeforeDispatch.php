<?php

namespace Easy\Mvc\Routing\Event;

use Easy\Network\Request;
use Symfony\Component\EventDispatcher\Event;

class BeforeDispatch extends Event
{

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

}
