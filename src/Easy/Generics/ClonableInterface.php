<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Generics;

/**
 * Supports cloning, which creates a new instance of a class with the same value as an existing instance.
 * 
 * @since 1.6.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
interface ClonableInterface
{

    /**
     * Creates a new object that is a copy of the current instance.
     */
    public function copy();
}
