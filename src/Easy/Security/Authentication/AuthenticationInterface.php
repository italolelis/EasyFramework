<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security\Authentication;

use Easy\Security\Authentication\Token\TokenInterface;

interface AuthenticationInterface
{

    /**
     * Do the logout
     */
    public function logout();

    /**
     * Check if the current user is authenticated
     */
    public function isAuthenticated();

    /**
     * Gets the current user
     */
    public function getUser();

    /**
     * Attempts to authenticates a TokenInterface object.
     * @param TokenInterface $token The token object
     */
    public function authenticate(TokenInterface $token);
}