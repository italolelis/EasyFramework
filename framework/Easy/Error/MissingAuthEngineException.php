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
 * Missing Auth engine exception - used when an Auth Engine
 * cannot be found.
 *
 * @package       Easy.Error
 */
class MissingAuthEngineException extends Exception
{

    protected $_messageTemplate = 'Auth engine %s could not be found.';

    public function __construct($message, $code = 404)
    {
        parent::__construct($message, $code);
    }

}
