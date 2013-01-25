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

use InvalidArgumentException;

/**
 * RedirectResponse represents an HTTP response doing a redirect.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RedirectResponse extends Response
{

    protected $targetUrl;

    /**
     * Creates a redirect response so that it conforms to the rules defined for a redirect status code.
     *
     * @param string  $url     The URL to redirect to
     * @param integer $status  The status code (302 by default)
     * @param array   $headers The headers (Location is always set to the given url)
     *
     * @throws InvalidArgumentException
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3
     */
    public function __construct($url, $status = 302, $headers = array())
    {
        if (empty($url)) {
            throw new InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        parent::__construct('', $status, $headers);

        $this->setTargetUrl($url);

        if (!$this->isRedirect()) {
            throw new InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function create($url = '', $status = 302, $headers = array())
    {
        return new static($url, $status, $headers);
    }

    /**
     * Returns the target URL.
     *
     * @return string target URL
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * Sets the redirect target of this response.
     *
     * @param string  $url     The URL to redirect to
     *
     * @return RedirectResponse The current response.
     *
     * @throws InvalidArgumentException
     */
    public function setTargetUrl($url)
    {
        if (empty($url)) {
            throw new InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->targetUrl = $url;

        $this->setContent(
                sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));

        $this->headers->set('Location', $url);

        return $this;
    }

}