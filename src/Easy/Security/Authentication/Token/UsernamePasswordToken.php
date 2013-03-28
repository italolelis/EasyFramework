<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Security\Authentication\Token;

use InvalidArgumentException;
use LogicException;

/**
 * UsernamePasswordToken implements a username and password token.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class UsernamePasswordToken extends AbstractToken {

    private $credentials;

    /**
     * Constructor.
     *
     * @param string          $user        The username (like a nickname, email address, etc.), or a UserInterface instance or an object implementing a __toString method.
     * @param string          $credentials This usually is the password of the user
     *
     * @throws InvalidArgumentException
     */
    public function __construct($user, $credentials) {
        $this->setUser($user);
        $this->credentials = $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthenticated($isAuthenticated) {
        if ($isAuthenticated) {
            throw new LogicException('Cannot set this token to trusted after instantiation.');
        }

        parent::setAuthenticated(false);
    }

    public function getCredentials() {
        return $this->credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize() {
        return serialize(array($this->credentials, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized) {
        list($this->credentials, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }

}