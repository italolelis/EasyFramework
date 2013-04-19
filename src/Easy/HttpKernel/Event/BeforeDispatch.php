<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\Event;

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
