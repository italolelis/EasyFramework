<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Collections;

use Countable;

/**
 * Defines size, enumerators, and synchronization methods for all nongeneric collections.
 */
interface CollectionInterface extends EnumerableInterface, Countable
{

    /**
     * Removes all elements from the IDictionary object.
     */
    public function clear();

    /**
     * Verifies whether a colletion is empty
     */
    public function IsEmpty();

    /**
     * Determines whether the IDictionary object contains an element with the specified key.
     * @param mixed $item The key to locate in the IDictionary object.
     */
    public function contains($item);
}