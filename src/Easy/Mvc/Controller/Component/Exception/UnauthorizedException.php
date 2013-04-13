<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller\Component\Exception;

use RuntimeException;

/**
 * Represents an HTTP 401 error.
 */
class UnauthorizedException extends RuntimeException
{

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Unauthorized' will be the message
     * @param string $code Status code, defaults to 401
     */
    public function __construct($message = null, $code = 401)
    {
        if (empty($message)) {
            $message = 'Unauthorized';
        }
        parent::__construct($message, $code);
    }

}
