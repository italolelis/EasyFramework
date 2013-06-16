<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Generics;

/**
 * Defines a generalized type-specific comparison method that a value type or class implements to order or sort its instances.
 * 
 * @since 2.1.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
interface ComparableInterface
{

    /**
     * Compares the current instance with another object of the same type and returns an integer that indicates whether the current instance precedes, follows, or occurs in the same position in the sort order as the other object.
     * @param object $obj An object to compare with this instance.
     */
    public function compareTo($obj);
}
