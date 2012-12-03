<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Generics;

/**
 * Defines a generalized method that a value type or class implements to create a type-specific method for determining equality of instances.
 */
interface IEquatable
{

    /**
     * Indicates whether the current object is equal to another object of the same type.
     * @param Object $obj The object to compare
     */
    public function equals($obj);
}
