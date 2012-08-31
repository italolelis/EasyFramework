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

use Easy\Serializer\IEncoder;
use Easy\Serializer\IDecoder;

/**
 * Encodes JSON data
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
abstract class JsonEncoder implements IEncoder, IDecoder
{

    /**
     * {@inheritdoc}
     */
    public static function encode($data, $format = 0)
    {
        return json_encode($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public static function decode($data, $format)
    {
        return json_decode($data);
    }

}
