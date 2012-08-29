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
 * Exception class to be thrown when a datasource configuration is not found
 *
 * @package       Easy.Error
 */
class MissingDatasourceConfigException extends Exception
{

    protected $_messageTemplate = 'The datasource configuration "%s" was not found in database.yml';

}
