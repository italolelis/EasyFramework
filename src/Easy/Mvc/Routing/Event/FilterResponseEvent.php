<?php

namespace Easy\Mvc\Routing\Event;

use Easy\HttpKernel\HttpKernelInterface;
use Easy\Network\Request;
use Easy\Network\Response;

class FilterResponseEvent extends KernelEvent {

    /**
     * The current response object
     * @var Response
     */
    protected $response;

    public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, Response $response) {
        parent::__construct($kernel, $request, $requestType);

        $this->setResponse($response);
    }

    /**
     * Returns the current response object
     *
     * @return Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Sets a new response object
     *
     * @param Response $response
     */
    public function setResponse(Response $response) {
        $this->response = $response;
    }

}
