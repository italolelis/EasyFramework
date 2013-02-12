<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
