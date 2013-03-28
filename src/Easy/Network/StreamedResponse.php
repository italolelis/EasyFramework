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

use Easy\Network\Request;
use Easy\Network\Response;
use LogicException;

/**
 * StreamedResponse represents a streamed HTTP response.
 *
 * A StreamedResponse uses a callback for its content.
 *
 * The callback should use the standard PHP functions like echo
 * to stream the response back to the client. The flush() method
 * can also be used if needed.
 *
 * @see flush()
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class StreamedResponse extends Response {

    protected $callback;
    protected $streamed;

    /**
     * Constructor.
     *
     * @param mixed   $callback A valid PHP callback
     * @param integer $status   The response status code
     * @param array   $headers  An array of response headers
     */
    public function __construct($callback = null, $status = 200, $headers = array()) {
        parent::__construct(null, $status, $headers);

        if (null !== $callback) {
            $this->setCallback($callback);
        }
        $this->streamed = false;
    }

    /**
     * {@inheritDoc}
     */
    public static function create($callback = null, $status = 200, $headers = array()) {
        return new static($callback, $status, $headers);
    }

    /**
     * Sets the PHP callback associated with this Response.
     *
     * @param mixed $callback A valid PHP callback
     *
     * @throws LogicException
     */
    public function setCallback($callback) {
        if (!is_callable($callback)) {
            throw new LogicException('The Response callback must be a valid PHP callable.');
        }
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Request $request) {
        $this->headers->set('Cache-Control', 'no-cache');

        return parent::prepare($request);
    }

    /**
     * {@inheritdoc}
     *
     * This method only sends the content once.
     */
    public function sendContent() {
        if ($this->streamed) {
            return;
        }

        $this->streamed = true;

        if (null === $this->callback) {
            throw new LogicException('The Response callback must not be null.');
        }

        call_user_func($this->callback);
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException when the content is not null
     */
    public function setContent($content) {
        if (null !== $content) {
            throw new LogicException('The content cannot be set on a StreamedResponse instance.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return false
     */
    public function getContent() {
        return false;
    }

}
