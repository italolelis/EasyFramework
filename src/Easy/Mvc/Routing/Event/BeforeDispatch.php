<?php

namespace Easy\Mvc\Routing\Event;

use Easy\Network\Request;
use Easy\Network\Response;
use Symfony\Component\EventDispatcher\Event;

class BeforeDispatch extends Event
{

    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

}
