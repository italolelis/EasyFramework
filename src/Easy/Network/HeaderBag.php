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

namespace Easy\Network;

use DateTime;
use Easy\Collections\Dictionary;
use RuntimeException;

/**
 * HeaderBag is a container for HTTP headers.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class HeaderBag extends Dictionary
{

    protected $cacheControl;

    public function __construct($array = null)
    {
        foreach ($array as $key => $value) {
            $newKey = strtr(strtolower($key), '_', '-');
            $array[$newKey] = $value;
            unset($array[$key]);
        }
        parent::__construct($array);
    }

    public function set($key, $value)
    {
        $key = strtr(strtolower($key), '_', '-');
        parent::set($key, $value);
    }

    public function getItem($key)
    {
        $key = strtr(strtolower($key), '_', '-');
        return parent::getItem($key);
    }

    public function contains($item)
    {
        $item = strtr(strtolower($item), '_', '-');
        return parent::contains($item);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheControlDirective($key)
    {
        return array_key_exists($key, $this->cacheControl);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheControlDirective($key)
    {
        return array_key_exists($key, $this->cacheControl) ? $this->cacheControl[$key] : null;
    }

    public function addCacheControlDirective($key, $value = true)
    {
        $this->cacheControl[$key] = $value;
    }

    public function removeCacheControlDirective($key)
    {
        unset($this->cacheControl[$key]);
    }

    /**
     * Returns the HTTP header value converted to a date.
     *
     * @param string    $key     The parameter key
     * @param DateTime $default The default value
     *
     * @return null|DateTime The parsed DateTime or the default value if the header does not exist
     *
     * @throws RuntimeException When the HTTP header is not parseable
     *
     * @api
     */
    public function getDate($key, DateTime $default = null)
    {
        if (null === $value = $this->getItem($key)) {
            return $default;
        }

        if (false === $date = DateTime::createFromFormat(DATE_RFC2822, $value)) {
            throw new RuntimeException(sprintf('The %s HTTP header is not parseable (%s).', $key, $value));
        }

        return $date;
    }

    /**
     * Parses a Cache-Control HTTP header.
     *
     * @param string $header The value of the Cache-Control HTTP header
     *
     * @return array An array representing the attribute values
     */
    protected function parseCacheControl($header)
    {
        $cacheControl = array();
        preg_match_all('#([a-zA-Z][a-zA-Z_-]*)\s*(?:=(?:"([^"]*)"|([^ \t",;]*)))?#', $header, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $cacheControl[strtolower($match[1])] = isset($match[3]) ? $match[3] : (isset($match[2]) ? $match[2] : true);
        }

        return $cacheControl;
    }

}
