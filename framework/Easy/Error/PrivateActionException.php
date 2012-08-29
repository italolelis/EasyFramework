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
 * Private Action exception - used when a controller action
 * starts with a  `_`.
 *
 * @package       Easy.Error
 */
class PrivateActionException extends Exception
{

    protected $_messageTemplate = 'Private Action %s::%s() is not directly accessible.';

    public function __construct($message, $code = 404, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
