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

use ArrayObject;
use InvalidArgumentException;

/**
 * Response represents an HTTP response in JSON format.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class JsonResponse extends Response
{

    protected $data;
    protected $callback;

    /**
     * Constructor.
     *
     * @param mixed   $data    The response data
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct($data = null, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        if (null === $data) {
            $data = new ArrayObject();
        }
        $this->setData($data);
    }

    /**
     * {@inheritDoc}
     */
    public static function create($data = null, $status = 200, $headers = array())
    {
        return new static($data, $status, $headers);
    }

    /**
     * Sets the JSONP callback.
     *
     * @param string $callback
     *
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     */
    public function setCallback($callback = null)
    {
        if (null !== $callback) {
            // taken from http://www.geekality.net/2011/08/03/valid-javascript-identifier/
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part)) {
                    throw new InvalidArgumentException('The callback name is not valid.');
                }
            }
        }

        $this->callback = $callback;

        return $this->update();
    }

    /**
     * Sets the data to be sent as json.
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    public function setData($data = array())
    {
        // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
        $this->data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        return $this->update();
    }

    /**
     * Updates the content and headers according to the json data and callback.
     *
     * @return JsonResponse
     */
    protected function update()
    {
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setContent(sprintf('%s(%s);', $this->callback, $this->data));
        }

        // Only set the header when there is none or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (!$this->headers->contains('Content-Type') || 'text/javascript' === $this->headers->getItem('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setContent($this->data);
    }

}