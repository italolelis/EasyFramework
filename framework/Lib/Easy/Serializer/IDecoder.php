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

/**
 * Defines the interface of encoders that are able to decode their own format
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface IDecoder
{

    /**
     * Decodes a string into PHP data
     *
     * @param string $data   Data to decode
     * @param string $format Format to decode from
     *
     * @return mixed
     */
    static function decode($data, $format);
}
