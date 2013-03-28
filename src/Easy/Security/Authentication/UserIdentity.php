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

namespace Easy\Security\Authentication;

use Easy\Collections\Collection;
use Easy\Security\Identity\PrincipalInterface;

/**
 * Represents a Logged User 
 */
class UserIdentity implements PrincipalInterface {

    /**
     * @var Collection 
     */
    private $roles;
    private $isAutenticated;

    public function getRoles() {
        return $this->roles;
    }

    /**
     * Set the roles for this IPrincipal
     * @param type $roles
     */
    public function setRoles(Collection $roles) {
        $this->roles = $roles;
    }

    /**
     * Sets if the current IPrincipal is authenticated
     * @param boolean $authenticated
     */
    public function setIsAuthenticated($authenticated) {
        $this->isAutenticated = $authenticated;
    }

    /**
     * Gets a value that indicates whether the user has been authenticated
     * @return boolean true if the user was authenticated; otherwise, false.
     */
    public function isAuthenticated() {
        return $this->isAutenticated;
    }

    /**
     * Checks if an IPrincipal object is guest
     * @return boolean
     */
    public function isGuest() {
        return !$this->isAuthenticated();
    }

    /**
     * Determines whether the current principal belongs to the specified role
     * @param string $role The name of the role for which to check membership
     * @return boolean true if the current principal is member of the specified role; otherwise false.
     */
    public function isInRole($role) {
        return $this->roles->contains($role);
    }

}