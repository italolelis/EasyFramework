<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\Event;

use Easy\HttpKernel\HttpKernelInterface;
use Easy\Network\Request;
use Exception;

/**
 * Allows to create a response for a thrown exception
 *
 * Call setResponse() to set the response that will be returned for the
 * current request. The propagation of this event is stopped as soon as a
 * response is set.
 *
 * You can also call setException() to replace the thrown exception. This
 * exception will be thrown if no response is set during processing of this
 * event.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class GetResponseForExceptionEvent extends GetResponseEvent
{

    /**
     * The exception object
     * @var Exception
     */
    private $exception;

    public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, Exception $e)
    {
        parent::__construct($kernel, $request, $requestType);

        $this->setException($e);
    }

    /**
     * Returns the thrown exception
     *
     * @return Exception  The thrown exception
     *
     * @api
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Replaces the thrown exception
     *
     * This exception will be thrown if no response is set in the event.
     *
     * @param Exception $exception The thrown exception
     *
     * @api
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;
    }

}
