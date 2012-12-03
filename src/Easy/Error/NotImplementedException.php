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
 * Not Implemented Exception - used when an API method is not implemented
 *
 * @package       Easy.Error
 */
class NotImplementedException extends Exception
{

    protected $_messageTemplate = '%s is not implemented.';

    public function __construct($message, $code = 501)
    {
        parent::__construct($message, $code);
    }

}
