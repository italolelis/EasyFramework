<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 1.6.2
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Serializer;

/**
 * Defines the interface json
 */
interface IJson
{

    /**
     * Encodes the specific object to JSON
     * 
     * @return string
     */
    function toJSON();
}
