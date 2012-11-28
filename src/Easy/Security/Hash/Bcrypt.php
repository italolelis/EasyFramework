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

namespace Easy\Security\Hash;

use Easy\Security\IHash;

/**
 * Bcrypt hashing class
 * 
 * @author Thiago Belem <contato@thiagobelem.net>
 * @link   https://gist.github.com/3438461
 */
class Bcrypt extends Hash implements IHash
{

    /**
     * Default salt prefix
     * 
     * @see http://www.php.net/security/crypt_blowfish.php
     * 
     * @var string
     */
    protected $_saltPrefix = '2a';

    /**
     * Default hashing cost (4-31)
     * 
     * @var integer
     */
    protected $_defaultCost = 8;

    /**
     * Salt limit length
     * 
     * @var integer
     */
    protected $_saltLength = 22;

    /**
     * Hash a string
     * 
     * @param  string  $string The string
     * @param  integer $cost   The hashing cost
     * 
     * @see    http://www.php.net/manual/en/function.crypt.php
     * 
     * @return string
     */
    public function hash($string)
    {
        $cost = $this->_defaultCost;

        // Salt
        $salt = $this->generateRandomSalt();

        // Hash string
        $hashString = $this->__generateHashString((int) $cost, $salt);

        return crypt($string, $hashString);
    }

    /**
     * Check a hashed string
     * 
     * @param  string $string The string
     * @param  string $hash   The hash
     * 
     * @return boolean
     */
    public function check($string, $hash)
    {
        return (crypt($string, $hash) === $hash);
    }

    /**
     * Generate a random base64 encoded salt
     * 
     * @return string
     */
    public function generateRandomSalt()
    {
        // Salt seed
        $seed = uniqid(mt_rand(), true);

        // Generate salt
        $salt = base64_encode($seed);
        $salt = str_replace('+', '.', $salt);

        return substr($salt, 0, $this->_saltLength);
    }

    /**
     * Build a hash string for crypt()
     * 
     * @param  integer $cost The hashing cost
     * @param  string $salt  The salt
     * 
     * @return string
     */
    private function __generateHashString($cost, $salt)
    {
        return sprintf('$%s$%02d$%s$', $this->_saltPrefix, $cost, $salt);
    }

}