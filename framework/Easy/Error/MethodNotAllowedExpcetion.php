<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 1.6
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Error;

/**
 * Represents an HTTP 405 error.
 *
 * @package       Easy.Error
 */
class MethodNotAllowedException extends HttpException
{

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Method Not Allowed' will be the message
     * @param string $code Status code, defaults to 405
     */
    public function __construct($message = null, $code = 405)
    {
        if (empty($message)) {
            $message = 'Method Not Allowed';
        }
        parent::__construct($message, $code);
    }

}
