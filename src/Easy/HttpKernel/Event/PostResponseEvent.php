<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\Event;

use Easy\HttpKernel\HttpKernelInterface;
use Easy\Network\Request;
use Easy\Network\Response;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic after a response was sent
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class PostResponseEvent extends Event
{

    /**
     * The kernel in which this event was thrown
     * @var HttpKernelInterface
     */
    private $kernel;
    private $request;
    private $response;

    public function __construct(HttpKernelInterface $kernel, Request $request, Response $response)
    {
        $this->kernel = $kernel;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Returns the kernel in which this event was thrown.
     *
     * @return HttpKernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns the request for which this event was thrown.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the response for which this event was thrown.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

}
