<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\Exception;

/**
 * UnauthorizedHttpException.
 *
 * @author Ben Ramsey <ben@benramsey.com>
 */
class UnauthorizedHttpException extends HttpException
{

    /**
     * Constructor.
     *
     * @param string     $challenge WWW-Authenticate challenge string
     * @param string     $message   The internal exception message
     * @param \Exception $previous  The previous exception
     * @param integer    $code      The internal exception code
     */
    public function __construct($challenge, $message = null, \Exception $previous = null, $code = 0)
    {
        $headers = array('WWW-Authenticate' => $challenge);

        parent::__construct(401, $message, $previous, $headers, $code);
    }

}
