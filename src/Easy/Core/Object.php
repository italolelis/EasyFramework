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

use Easy\Generics\IClonable;
use Easy\Generics\IEquatable;
use Easy\Generics\IFormattable;
use Serializable;

/**
 * Object class provides a few generic methods used in several subclasses.
 */
class Object implements Serializable, IEquatable, IClonable, IFormattable
{

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return get_class($this);
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize($this);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

    /**
     * @inheritdoc
     */
    public function copy()
    {
        return clone($this);
    }

}
