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
 * Supports cloning, which creates a new instance of a class with the same value as an existing instance.
 */
interface IClonable
{

    /**
     * Creates a new object that is a copy of the current instance.
     */
    public function copy();
}
