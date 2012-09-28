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
 * Represents an HTTP 500 error.
 *
 * @package Easy.Error
 */
class InvalidArgumentException extends HttpException
{

    public function __construct($message = null, $code = 500)
    {
        parent::__construct($message, $code);
    }

}
