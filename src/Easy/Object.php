<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy;

use Easy\Generics\ClonableInterface;
use Easy\Generics\EquatableInterface;
use Easy\Generics\FormattableInterface;
use Serializable;

/**
 * Object class provides a few generic methods used in several subclasses.
 */
class Object implements Serializable, EquatableInterface, ClonableInterface, FormattableInterface
{

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function copy()
    {
        return clone($this);
    }

}
