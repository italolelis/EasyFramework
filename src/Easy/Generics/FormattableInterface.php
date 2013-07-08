<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Generics;

/**
 * Provides functionality to format the value of an object into a string representation.
 *
 * @since 2.0.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
interface FormattableInterface
{

    /**
     * Formats the value of the current instance using the specified format.
     *
     * @return String A String containing the value of the current instance in the specified format.
     */
    function toString();
}