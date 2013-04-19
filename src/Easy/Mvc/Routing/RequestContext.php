<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Routing;

use Easy\Network\Request;
use Symfony\Component\Routing\RequestContext as BaseRequestContext;

class RequestContext extends BaseRequestContext
{

    public function fromEasyRequest(Request $request)
    {
        $this->setBaseUrl($request->getBaseUrl());
        $this->setPathInfo($request->getPathInfo());
        $this->setMethod($request->getMethod());
        $this->setHost($request->getHost());
        $this->setScheme($request->getScheme());
        $this->setHttpPort($request->isSecure() ? $this->getHttpPort() : $request->getPort());
        $this->setHttpsPort($request->isSecure() ? $request->getPort() : $this->getHttpsPort());
    }

}