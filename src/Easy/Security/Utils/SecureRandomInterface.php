<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security\Utils;

/**
 * Interface that needs to be implemented by all secure random number generators.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface SecureRandomInterface
{

    /**
     * Generates the specified number of secure random bytes.
     *
     * @param integer $nbBytes
     *
     * @return string
     */
    public function nextBytes($nbBytes);
}