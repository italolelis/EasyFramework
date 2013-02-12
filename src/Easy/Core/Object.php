<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
