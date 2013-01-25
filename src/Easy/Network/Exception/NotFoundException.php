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

namespace Easy\Network\Exception;

/**
 * Represents an HTTP 404 error.
 *
 * @package       Easy.Error
 */
class NotFoundException extends HttpException
{

    /**
     * Constructor
     *
     * @param string $message If no message is given 'Not Found' will be the message
     * @param string $code Status code, defaults to 404
     */
    public function __construct($message = null, \Exception $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }

}
