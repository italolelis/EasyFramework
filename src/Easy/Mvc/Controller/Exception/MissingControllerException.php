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

namespace Easy\Mvc\Controller\Exception;

/**
 * Missing Controller exception - used when a controller
 * cannot be found.
 *
 * @package       Easy.Error
 */
class MissingControllerException extends ControllerException
{

    protected $_messageTemplate = 'Controller class %s could not be found.';

    public function __construct($message, $code = 404)
    {
        parent::__construct($message, $code);
    }

}
