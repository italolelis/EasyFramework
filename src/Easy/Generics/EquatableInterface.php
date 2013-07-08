<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Generics;

/**
 * Defines a generalized method that a value type or class implements to create a type-specific method for determining equality of instances.
 * 
 * @since 1.6.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
interface EquatableInterface
{

    /**
     * Indicates whether the current object is equal to another object of the same type.
     * @param Object $obj The object to compare
     */
    public function equals($obj);
}
