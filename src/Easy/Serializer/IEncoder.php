<?php

/**
 * 
 * FROM SYMFONY
 * 
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 1.6
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Serializer;

/**
 * Defines the interface of encoders
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface IEncoder
{

    /**
     * Encodes data into a string
     *
     * @param mixed $data    Data to encode
     * @param string $format Format to encode to
     *
     * @return string
     */
    static function encode($data, $format = null);
}
