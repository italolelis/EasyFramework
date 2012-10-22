<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 0.5
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Core;

use Easy\Generics\IEquatable;
use Serializable;

/**
 * Object class provides a few generic methods used in several subclasses.
 *
 * Also includes methods for logging and the special method RequestAction,
 * to call other Controllers' Actions from anywhere.
 *
 * @package       Easy.Core
 */
class Object implements Serializable, IEquatable, \Easy\Generics\IClonable
{

    /**
     * constructor, no-op
     *
     */
    public function __construct()
    {
        
    }

    /**
     * Object-to-string conversion.
     * Each class can override this method as necessary.
     *
     * @return string The name of this class
     */
    public function toString()
    {
        return get_class($this);
    }

    public function equals($obj)
    {
        return ($this === $obj);
    }

    /**
     * Stop execution of the current script.  Wraps exit() making
     * testing easier.
     *
     * @param integer|string $status see http://php.net/exit for values
     * @return void
     */
    protected function finalize($status = 0)
    {
        exit($status);
    }

    public function serialize()
    {
        return serialize($this);
    }

    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

    public function copy()
    {
        return clone($this);
    }

}