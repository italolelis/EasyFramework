<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Security\Authentication\Token;

use Serializable;

/**
 * TokenInterface is the interface for the user authentication information.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface TokenInterface extends Serializable
{

    /**
     * Returns a string representation of the Token.
     *
     * This is only to be used for debugging purposes.
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials();

    /**
     * Returns a user representation.
     *
     * @return mixed either returns an object which implements __toString(), or a primitive string is returned.
     */
    public function getUser();

    /**
     * Sets a user.
     *
     * @param mixed $user
     */
    public function setUser($user);

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername();
}