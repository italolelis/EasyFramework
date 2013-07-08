<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security\Identity;

/**
 * Defines the basic funcionality of an identity object 
 */
interface IdentityInterface
{

    /**
     * Gets a value that indicates whether the user has been authenticated
     * @return boolean true if the user was authenticated; otherwise, false.
     */
    public function isAuthenticated();
}
