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
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

/**
 * Response is responsible for managing the response text, status and headers of a HTTP response.
 *
 * By default controllers will use this class to render their response. If you are going to use
 * a custom response class it should subclass this object in order to ensure compatibility.
 *
 */
class Response
{

    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2012-02-13).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing', // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status', // RFC4918
        208 => 'Already Reported', // RFC5842
        226 => 'IM Used', // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect', // RFC-reschke-http-status-308-07
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot', // RFC2324
        422 => 'Unprocessable Entity', // RFC4918
        423 => 'Locked', // RFC4918
        424 => 'Failed Dependency', // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal', // RFC2817
        426 => 'Upgrade Required', // RFC2817
        428 => 'Precondition Required', // RFC6585
        429 => 'Too Many Requests', // RFC6585
        431 => 'Request Header Fields Too Large', // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)', // RFC2295
        507 => 'Insufficient Storage', // RFC4918
        508 => 'Loop Detected', // RFC5842
        510 => 'Not Extended', // RFC2774
        511 => 'Network Authentication Required', // RFC6585
    );

    /**
     * @var array
     */
    protected $_mimeTypes = array(
        'ai' => 'application/postscript',
        'bcpio' => 'application/x-bcpio',
        'bin' => 'application/octet-stream',
        'ccad' => 'application/clariscad',
        'cdf' => 'application/x-netcdf',
        'class' => 'application/octet-stream',
        'cpio' => 'application/x-cpio',
        'cpt' => 'application/mac-compactpro',
        'csh' => 'application/x-csh',
        'csv' => array('text/csv', 'application/vnd.ms-excel', 'text/plain'),
        'dcr' => 'application/x-director',
        'dir' => 'application/x-director',
        'dms' => 'application/octet-stream',
        'doc' => 'application/msword',
        'drw' => 'application/drafting',
        'dvi' => 'application/x-dvi',
        'dwg' => 'application/acad',
        'dxf' => 'application/dxf',
        'dxr' => 'application/x-director',
        'eot' => 'application/vnd.ms-fontobject',
        'eps' => 'application/postscript',
        'exe' => 'application/octet-stream',
        'ez' => 'application/andrew-inset',
        'flv' => 'video/x-flv',
        'gtar' => 'application/x-gtar',
        'gz' => 'application/x-gzip',
        'bz2' => 'application/x-bzip',
        '7z' => 'application/x-7z-compressed',
        'hdf' => 'application/x-hdf',
        'hqx' => 'application/mac-binhex40',
        'ico' => 'image/vnd.microsoft.icon',
        'ips' => 'application/x-ipscript',
        'ipx' => 'application/x-ipix',
        'js' => 'text/javascript',
        'latex' => 'application/x-latex',
        'lha' => 'application/octet-stream',
        'lsp' => 'application/x-lisp',
        'lzh' => 'application/octet-stream',
        'man' => 'application/x-troff-man',
        'me' => 'application/x-troff-me',
        'mif' => 'application/vnd.mif',
        'ms' => 'application/x-troff-ms',
        'nc' => 'application/x-netcdf',
        'oda' => 'application/oda',
        'otf' => 'font/otf',
        'pdf' => 'application/pdf',
        'pgn' => 'application/x-chess-pgn',
        'pot' => 'application/mspowerpoint',
        'pps' => 'application/mspowerpoint',
        'ppt' => 'application/mspowerpoint',
        'ppz' => 'application/mspowerpoint',
        'pre' => 'application/x-freelance',
        'prt' => 'application/pro_eng',
        'ps' => 'application/postscript',
        'roff' => 'application/x-troff',
        'scm' => 'application/x-lotusscreencam',
        'set' => 'application/set',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'sit' => 'application/x-stuffit',
        'skd' => 'application/x-koan',
        'skm' => 'application/x-koan',
        'skp' => 'application/x-koan',
        'skt' => 'application/x-koan',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'sol' => 'application/solids',
        'spl' => 'application/x-futuresplash',
        'src' => 'application/x-wais-source',
        'step' => 'application/STEP',
        'stl' => 'application/SLA',
        'stp' => 'application/STEP',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'swf' => 'application/x-shockwave-flash',
        't' => 'application/x-troff',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tr' => 'application/x-troff',
        'tsp' => 'application/dsptype',
        'ttf' => 'font/ttf',
        'unv' => 'application/i-deas',
        'ustar' => 'application/x-ustar',
        'vcd' => 'application/x-cdlink',
        'vda' => 'application/vda',
        'xlc' => 'application/vnd.ms-excel',
        'xll' => 'application/vnd.ms-excel',
        'xlm' => 'application/vnd.ms-excel',
        'xls' => 'application/vnd.ms-excel',
        'xlw' => 'application/vnd.ms-excel',
        'zip' => 'application/zip',
        'aif' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'au' => 'audio/basic',
        'kar' => 'audio/midi',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mp2' => 'audio/mpeg',
        'mp3' => 'audio/mpeg',
        'mpga' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'ra' => 'audio/x-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'snd' => 'audio/basic',
        'tsi' => 'audio/TSP-audio',
        'wav' => 'audio/x-wav',
        'asc' => 'text/plain',
        'c' => 'text/plain',
        'cc' => 'text/plain',
        'css' => 'text/css',
        'etx' => 'text/x-setext',
        'f' => 'text/plain',
        'f90' => 'text/plain',
        'h' => 'text/plain',
        'hh' => 'text/plain',
        'html' => array('text/html', '*/*'),
        'htm' => array('text/html', '*/*'),
        'm' => 'text/plain',
        'rtf' => 'text/rtf',
        'rtx' => 'text/richtext',
        'sgm' => 'text/sgml',
        'sgml' => 'text/sgml',
        'tsv' => 'text/tab-separated-values',
        'tpl' => 'text/template',
        'txt' => 'text/plain',
        'text' => 'text/plain',
        'xml' => array('application/xml', 'text/xml'),
        'avi' => 'video/x-msvideo',
        'fli' => 'video/x-fli',
        'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'qt' => 'video/quicktime',
        'viv' => 'video/vnd.vivo',
        'vivo' => 'video/vnd.vivo',
        'gif' => 'image/gif',
        'ief' => 'image/ief',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'pbm' => 'image/x-portable-bitmap',
        'pgm' => 'image/x-portable-graymap',
        'png' => 'image/png',
        'pnm' => 'image/x-portable-anymap',
        'ppm' => 'image/x-portable-pixmap',
        'ras' => 'image/cmu-raster',
        'rgb' => 'image/x-rgb',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'ice' => 'x-conference/x-cooltalk',
        'iges' => 'model/iges',
        'igs' => 'model/iges',
        'mesh' => 'model/mesh',
        'msh' => 'model/mesh',
        'silo' => 'model/mesh',
        'vrml' => 'model/vrml',
        'wrl' => 'model/vrml',
        'mime' => 'www/mime',
        'pdb' => 'chemical/x-pdb',
        'xyz' => 'chemical/x-pdb',
        'javascript' => 'text/javascript',
        'json' => 'application/json',
        'form' => 'application/x-www-form-urlencoded',
        'file' => 'multipart/form-data',
        'xhtml' => array('application/xhtml+xml', 'application/xhtml', 'text/xhtml'),
        'xhtml-mobile' => 'application/vnd.wap.xhtml+xml',
        'rss' => 'application/rss+xml',
        'atom' => 'application/atom+xml',
        'amf' => 'application/x-amf',
        'wap' => array('text/vnd.wap.wml', 'text/vnd.wap.wmlscript', 'image/vnd.wap.wbmp'),
        'wml' => 'text/vnd.wap.wml',
        'wmlscript' => 'text/vnd.wap.wmlscript',
        'wbmp' => 'image/vnd.wap.wbmp',
    );

    /**
     * @var string
     */
    protected $protocol = 'HTTP/1.1';

    /**
     * @var integer
     */
    protected $statusCode = 200;

    /**
     * @var string
     */
    protected $statusText;

    /**
     * Content type to send. This can be an 'extension' that will be transformed using the $_mimetypes array
     * or a complete mime-type
     *
     * @var integer
     */
    protected $_contentType = 'text/html';

    /**
     * Holds cookies to be sent to the client
     * 
     * @var array
     */
    protected $_cookies = array();

    /**
     * @var ResponseHeaderBag
     */
    public $headers;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * @var array
     */
    protected $_cacheDirectives = array();

    /**
     * Class constructor
     *
     * @param array $options list of parameters to setup the response. Possible values are:
     * 	- body: the response text that should be sent to the client
     * 	- status: the HTTP status code to respond with
     * 	- type: a complete mime-type string or an extension mapped in this class
     * 	- charset: the charset for the response body
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = new ResponseHeaderBag($headers);
        $this->setContent($content);
        $this->setStatusCode($status);
        if (!$this->headers->contains('Date')) {
            $this->setDate(new \DateTime(null, new \DateTimeZone('UTC')));
        }
    }

    /**
     * Factory method for chainability
     *
     * Example:
     *
     *     return Response::create($body, 200)
     *         ->setSharedMaxAge(300);
     *
     * @param string  $content The response content
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     *
     * @return Response
     */
    public static function create($content = '', $status = 200, $headers = array())
    {
        return new static($content, $status, $headers);
    }

    /**
     * Prepares the Response before it is sent to the client.
     *
     * This method tweaks the Response to ensure that it is
     * compliant with RFC 2616. Most of the changes are based on
     * the Request that is "associated" with this Response.
     *
     * @param Request $request A Request instance
     *
     * @return Response The current response.
     */
    public function prepare(Request $request)
    {
        if ($this->isInformational() || in_array($this->statusCode, array(204, 304))) {
            $this->setContent(null);
        }

        // Content-type based on the Request
        if (!$this->headers->contains('Content-Type')) {
            $format = $request->getRequestFormat();
            if (null !== $format && $mimeType = $request->getMimeType($format)) {
                $this->headers->set('Content-Type', $mimeType);
            }
        }

        // Fix Content-Type
        if (strpos($this->_contentType, 'text/') === 0) {
            $this->headers->set('Content-Type', "{$this->_contentType}; charset={$this->charset}");
        } else {
            $this->headers->set('Content-Type', "{$this->_contentType}");
        }

        // Fix Content-Length
        if ($this->headers->contains('Transfer-Encoding')) {
            $this->headers->remove('Content-Length');
        }

        if ($request->isMethod('HEAD')) {
            // cf. RFC2616 14.13
            $length = $this->headers->getItem('Content-Length');
            $this->setContent(null);
            if ($length) {
                $this->headers->set('Content-Length', $length);
            }
        }

        // Fix protocol
//        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
//            $this->setProtocolVersion('1.1');
//        }

        return $this;
    }

    /**
     * Sends the complete response to the client including headers and message body.
     * Will echo out the content in the response body.
     *
     * @return void
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent($this->content);

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            // ob_get_level() never returns 0 on some Windows configurations, so if
            // the level is the same two times in a row, the loop should be stopped.
            $previous = null;
            $obStatus = ob_get_status(1);
            while (($level = ob_get_level()) > 0 && $level !== $previous) {
                $previous = $level;
                if ($obStatus[$level - 1] && isset($obStatus[$level - 1]['del']) && $obStatus[$level - 1]['del']) {
                    ob_end_flush();
                }
            }
            flush();
        }

        return $this;
    }

    /**
     * Sends HTTP headers.
     *
     * @return Response
     */
    protected function sendHeaders()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }
        // status
        header(sprintf('HTTP/%s %s %s', $this->protocol, $this->statusCode, $this->statusText));
        // headers
        foreach ($this->headers as $header => $values) {
            foreach ($values as $value) {
                header("{$header}: {$value}", false);
            }
        }
        // cookies
        foreach ($this->headers->getCookies() as $cookie) {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }
    }

    /**
     * Sends content for the current web response.
     *
     * @return Response
     */
    protected function sendContent($content)
    {
        echo $content;
        return $this;
    }

    /**
     * Gets the current response content.
     *
     * @return string Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the response content.
     *
     * Valid types are strings, numbers, and objects that implement a __toString() method.
     *
     * @param mixed $content
     *
     * @return Response
     *
     * @throws UnexpectedValueException
     */
    public function setContent($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array($content, '__toString'))) {
            throw new UnexpectedValueException('The Response content must be a string or object implementing __toString(), "' . gettype($content) . '" given.');
        }

        $this->content = (string) $content;
        return $this;
    }

    /**
     * Sets the response status code.
     *
     * @param integer $code HTTP status code
     * @param mixed   $text HTTP status text
     *
     * If the status text is null it will be automatically populated for the known
     * status codes and left empty otherwise.
     *
     * @return Response
     *
     * @throws InvalidArgumentException When the HTTP status code is not valid
     *
     * @api
     */
    public function setStatusCode($code, $text = null)
    {

        $this->statusCode = $code = (int) $code;

        if ($this->isInvalid()) {
            throw new InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        if (null === $text) {
            $this->statusText = isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : '';

            return $this;
        }

        if (false === $text) {
            $this->statusText = '';
            return $this;
        }

        $this->statusText = $text;

        return $this;
    }

    /**
     * Retrieves the status code for the current web response.
     *
     * @return integer Status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the response content type. It can be either a file extension
     * which will be mapped internally to a mime-type or a string representing a mime-type
     * if $contentType is null the current content type is returned
     * if $contentType is an associative array, it will be stored as a content type definition
     *
     * ### Setting the content type
     *
     * e.g `type('jpg');`
     *
     * ### Returning the current content type
     *
     * e.g `type();`
     *
     * ### Storing a content type definition
     *
     * e.g `type(array('keynote' => 'application/keynote'));`
     *
     * ### Replacing a content type definition
     *
     * e.g `type(array('jpg' => 'text/plain'));`
     *
     * @param string $contentType
     * @return mixed current content type or false if supplied an invalid content type
     */
    public function type($contentType = null)
    {
        if (is_null($contentType)) {
            return $this->_contentType;
        }
        if (is_array($contentType)) {
            $type = key($contentType);
            $defitition = current($contentType);
            $this->_mimeTypes[$type] = $defitition;
            return $this->_contentType;
        }
        if (isset($this->_mimeTypes[$contentType])) {
            $contentType = $this->_mimeTypes[$contentType];
            $contentType = is_array($contentType) ? current($contentType) : $contentType;
        }
        if (strpos($contentType, '/') === false) {
            return false;
        }
        return $this->_contentType = $contentType;
    }

    /**
     * Returns the mime type definition for an alias
     *
     * e.g `getMimeType('pdf'); // returns 'application/pdf'`
     *
     * @param string $alias the content type alias to map
     * @return mixed string mapped mime type or false if $alias is not mapped
     */
    public function getMimeType($alias)
    {
        if (isset($this->_mimeTypes[$alias])) {
            return $this->_mimeTypes[$alias];
        }
        return false;
    }

    /**
     * Returns the Date header as a DateTime instance.
     *
     * @return \DateTime A \DateTime instance
     *
     * @throws \RuntimeException When the header is not parseable
     *
     * @api
     */
    public function getDate()
    {
        return $this->headers->getDate('Date', new \DateTime());
    }

    /**
     * Maps a content-type back to an alias
     *
     * e.g `mapType('application/pdf'); // returns 'pdf'`
     *
     * @param mixed $ctype Either a string content type to map, or an array of types.
     * @return mixed Aliases for the types provided.
     */
    public function mapType($ctype)
    {
        if (is_array($ctype)) {
            return array_map(array($this, 'mapType'), $ctype);
        }

        foreach ($this->_mimeTypes as $alias => $types) {
            if (is_array($types) && in_array($ctype, $types)) {
                return $alias;
            } elseif (is_string($types) && $types == $ctype) {
                return $alias;
            }
        }
        return null;
    }

    /**
     * Sets the response charset.
     *
     * @param string $charset Character set
     * @return Response
     */
    public function setCharset($charset = null)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Retrieves the status code for the current web response.
     *
     * @return integer Status code
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Sets the correct headers to instruct the client to not cache the response
     *
     * @return void
     */
    public function disableCache()
    {
        $this->headers->set('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $this->headers->set('Last-Modified', gmdate("D, d M Y H:i:s") . " GMT");
        $this->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    /**
     * Returns true if the response is worth caching under any circumstance.
     *
     * Responses marked "private" with an explicit Cache-Control directive are
     * considered uncacheable.
     *
     * Responses with neither a freshness lifetime (Expires, max-age) nor cache
     * validator (Last-Modified, ETag) are considered uncacheable.
     *
     * @return Boolean true if the response is worth caching, false otherwise
     *
     * @api
     */
    public function isCacheable()
    {
        if (!in_array($this->statusCode, array(200, 203, 300, 301, 302, 404, 410))) {
            return false;
        }

        if ($this->headers->hasCacheControlDirective('no-store') || $this->headers->getCacheControlDirective('private')) {
            return false;
        }

        return $this->isValidateable() || $this->isFresh();
    }

    /**
     * Sets the response's cache headers (validation and/or expiration).
     *
     * Available options are: etag, last_modified, max_age, s_maxage, private, and public.
     *
     * @param array $options An array of cache options
     *
     * @return Response
     *
     * @throws \InvalidArgumentException
     *
     * @api
     */
    public function setCache(array $options)
    {
        if ($diff = array_diff(array_keys($options), array('etag', 'last_modified', 'max_age', 's_maxage', 'private', 'public'))) {
            throw new \InvalidArgumentException(sprintf('Response does not support the following options: "%s".', implode('", "', array_values($diff))));
        }

        if (isset($options['etag'])) {
            $this->setEtag($options['etag']);
        }

        if (isset($options['last_modified'])) {
            $this->setLastModified($options['last_modified']);
        }

        if (isset($options['max_age'])) {
            $this->setMaxAge($options['max_age']);
        }

        if (isset($options['s_maxage'])) {
            $this->setSharedMaxAge($options['s_maxage']);
        }

        if (isset($options['public'])) {
            if ($options['public']) {
                $this->setPublic();
            } else {
                $this->setPrivate();
            }
        }

        if (isset($options['private'])) {
            if ($options['private']) {
                $this->setPrivate();
            } else {
                $this->setPublic();
            }
        }

        return $this;
    }

    /**
     * Sets whether a response is eligible to be cached by intermediate proxies
     * This method controls the `public` or `private` directive in the Cache-Control
     * header
     *
     * @param boolean $public  if set to true, the Cache-Control header will be set as public
     * if set to false, the response will be set to private
     * if no value is provided, it will return whether the response is sharable or not
     * @param int $time time in seconds after which the response should no longer be considered fresh
     * @return boolean
     */
    public function sharable($public = null, $time = null)
    {
        if ($public === null) {
            $public = array_key_exists('public', $this->_cacheDirectives);
            $private = array_key_exists('private', $this->_cacheDirectives);
            $noCache = array_key_exists('no-cache', $this->_cacheDirectives);
            if (!$public && !$private && !$noCache) {
                return null;
            }
            $sharable = $public || !($private || $noCache);
            return $sharable;
        }
        if ($public) {
            $this->_cacheDirectives['public'] = true;
            unset($this->_cacheDirectives['private']);
            $this->setSharedMaxAge($time);
        } else {
            $this->_cacheDirectives['private'] = true;
            unset($this->_cacheDirectives['public']);
            $this->setMaxAge($time);
        }
        if ($time == null) {
            $this->_setCacheControl();
        }
        return (bool) $public;
    }

    /**
     * Returns the number of seconds after the time specified in the response's Date
     * header when the the response should no longer be considered fresh.
     *
     * First, it checks for a s-maxage directive, then a max-age directive, and then it falls
     * back on an expires header. It returns null when no maximum age can be established.
     *
     * @return integer|null Number of seconds
     *
     * @api
     */
    public function getMaxAge()
    {
        if ($this->headers->hasCacheControlDirective('s-maxage')) {
            return (int) $this->headers->getCacheControlDirective('s-maxage');
        }

        if ($this->headers->hasCacheControlDirective('max-age')) {
            return (int) $this->headers->getCacheControlDirective('max-age');
        }

        if (null !== $this->getExpires()) {
            return $this->getExpires()->format('U') - $this->getDate()->format('U');
        }

        return null;
    }

    /**
     * Sets the Date header.
     *
     * @param \DateTime $date A \DateTime instance
     *
     * @return Response
     *
     * @api
     */
    public function setDate(\DateTime $date)
    {
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Date', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh.
     *
     * This methods sets the Cache-Control max-age directive.
     *
     * @param integer $value Number of seconds
     *
     * @return Response
     *
     * @api
     */
    public function setMaxAge($value)
    {
        $this->headers->addCacheControlDirective('max-age', $value);

        return $this;
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh by shared caches.
     *
     * This methods sets the Cache-Control s-maxage directive.
     *
     * @param integer $value Number of seconds
     *
     * @return Response
     *
     * @api
     */
    public function setSharedMaxAge($value)
    {
        $this->setPublic();
        $this->headers->addCacheControlDirective('s-maxage', $value);

        return $this;
    }

    /**
     * Returns the age of the response.
     *
     * @return integer The age of the response in seconds
     */
    public function getAge()
    {
        if (null !== $age = $this->headers->getItem('Age')) {
            return (int) $age;
        }

        return max(time() - $this->getDate()->format('U'), 0);
    }

    /**
     * Marks the response stale by setting the Age header to be equal to the maximum age of the response.
     *
     * @return Response
     *
     * @api
     */
    public function expire()
    {
        if ($this->isFresh()) {
            $this->headers->set('Age', $this->getMaxAge());
        }

        return $this;
    }

    /**
     * Returns the value of the Expires header as a DateTime instance.
     *
     * @return \DateTime|null A DateTime instance or null if the header does not exist
     *
     * @api
     */
    public function getExpires()
    {
        try {
            return $this->headers->getDate('Expires');
        } catch (\RuntimeException $e) {
            // according to RFC 2616 invalid date formats (e.g. "0" and "-1") must be treated as in the past
            return \DateTime::createFromFormat(DATE_RFC2822, 'Sat, 01 Jan 00 00:00:00 +0000');
        }
    }

    /**
     * Sets the Expires HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     *
     * @param \DateTime|null $date A \DateTime instance or null to remove the header
     *
     * @return Response
     *
     * @api
     */
    public function setExpires(\DateTime $date = null)
    {
        if (null === $date) {
            $this->headers->remove('Expires');
        } else {
            $date = clone $date;
            $date->setTimezone(new \DateTimeZone('UTC'));
            $this->headers->set('Expires', $date->format('D, d M Y H:i:s') . ' GMT');
        }

        return $this;
    }

    /**
     * Returns true if the response includes a Vary header.
     *
     * @return Boolean true if the response includes a Vary header, false otherwise
     *
     * @api
     */
    public function hasVary()
    {
        return null !== $this->headers->getItem('Vary');
    }

    /**
     * Returns an array of header names given in the Vary header.
     *
     * @return array An array of Vary names
     *
     * @api
     */
    public function getVary()
    {
        if (!$vary = $this->headers->getItem('Vary')) {
            return array();
        }

        return is_array($vary) ? $vary : preg_split('/[\s,]+/', $vary);
    }

    /**
     * Sets the Vary header.
     *
     * @param string|array $headers
     * @param Boolean      $replace Whether to replace the actual value of not (true by default)
     *
     * @return Response
     *
     * @api
     */
    public function setVary($headers, $replace = true)
    {
        $this->headers->set('Vary', $headers, $replace);

        return $this;
    }

    /**
     * Sets the correct output buffering handler to send a compressed response. Responses will
     * be compressed with zlib, if the extension is available.
     *
     * @return boolean false if client does not accept compressed responses or no handler is available, true otherwise
     */
    public function compress()
    {
        $compressionEnabled = ini_get("zlib.output_compression") !== '1' &&
                extension_loaded("zlib") &&
                (strpos(env('HTTP_ACCEPT_ENCODING'), 'gzip') !== false);
        return $compressionEnabled && ob_start('ob_gzhandler');
    }

    /**
     * Returns whether the resulting output will be compressed by PHP
     *
     * @return boolean
     */
    public function isOutputCompressed()
    {
        return strpos(env('HTTP_ACCEPT_ENCODING'), 'gzip') !== false && (ini_get("zlib.output_compression") === '1' || in_array('ob_gzhandler', ob_list_handlers()));
    }

    /**
     * Sets the correct headers to instruct the browser to dowload the response as a file.
     *
     * @param string $filename the name of the file as the browser will download the response
     * @return void
     */
    public function download($filename)
    {
        $this->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Sets the Content-Length header for the response
     * If called with no arguments returns the last Content-Length set
     *
     * @return int
     */
    public function setLength($bytes = null)
    {
        if ($bytes !== null) {
            $this->headers->set('Content-Length', $bytes);
        }
        return $this;
    }

    /**
     * Modifies the response so that it conforms to the rules defined for a 304 status code.
     *
     * This sets the status, removes the body, and discards any headers
     * that MUST NOT be included in 304 responses.
     *
     * @return Response
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3.5
     */
    public function setNotModified()
    {
        $this->setStatusCode(304);
        $this->setContent(null);

        // remove headers that MUST NOT be included with 304 Not Modified responses
        foreach (array('Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified') as $header) {
            $this->headers->remove($header);
        }

        return $this;
    }

    /**
     * Determines if the Response validators (ETag, Last-Modified) match
     * a conditional value specified in the Request.
     *
     * If the Response is not modified, it sets the status code to 304 and
     * removes the actual content by calling the setNotModified() method.
     *
     * @param Request $request A Request instance
     *
     * @return Boolean true if the Response validators match the Request, false otherwise
     */
    public function isNotModified(Request $request)
    {
        if (!$request->isMethodSafe()) {
            return false;
        }
        $lastModified = null;
        if ($request->headers->contains('If-Modified-Since')) {
            $lastModified = $request->headers->getItem('If-Modified-Since');
        }
        $notModified = false;
        if ($etags = $request->getEtags()) {
            $notModified = (in_array($this->getEtag(), $etags) || in_array('*', $etags)) && (!$lastModified || $this->headers->getItem('Last-Modified') == $lastModified);
        } elseif ($lastModified) {
            $notModified = $lastModified == $this->headers->getItem('Last-Modified');
        }

        if ($notModified) {
            $this->setNotModified();
        }

        return $notModified;
    }

    /**
     * Helper method to generate a valid Cache-Control header from the options set in other methods
     *
     * @return void
     */
    protected function _setCacheControl()
    {
        $control = '';
        foreach ($this->_cacheDirectives as $key => $val) {
            $control .= $val === true ? $key : sprintf('%s=%s', $key, $val);
            $control .= ', ';
        }
        $control = rtrim($control, ', ');
        $this->headers->set('Cache-Control', $control);
    }

    /**
     * Returns the Last-Modified HTTP header as a DateTime instance.
     *
     * @return \DateTime|null A DateTime instance or null if the header does not exist
     *
     * @throws RuntimeException When the HTTP header is not parseable
     */
    public function getLastModified()
    {
        return $this->headers->getDate('Last-Modified');
    }

    /**
     * Sets the Last-Modified HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     *
     * @param \DateTime|null $date A \DateTime instance or null to remove the header
     * @return Response
     */
    public function setModified(\DateTime $date = null)
    {
        if (null === $date) {
            $this->headers->remove('Last-Modified');
        } else {
            $date = clone $date;
            $date->setTimezone(new \DateTimeZone('UTC'));
            $this->headers->set('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');
        }

        return $this;
    }

    /**
     * Sets the response as Not Modified by removing any body contents 
     * setting the status code to "304 Not Modified" and removing all 
     * conflicting headers
     *
     * @return void
     * */
    public function notModified()
    {
        $this->setStatusCode(304);
        $this->getContent('');
        $remove = array(
            'Allow',
            'Content-Encoding',
            'Content-Language',
            'Content-Length',
            'Content-MD5',
            'Content-Type',
            'Last-Modified'
        );
        foreach ($remove as $header) {
            $this->headers->remove($header);
        }
    }

    /**
     * Returns the response's time-to-live in seconds.
     *
     * It returns null when no freshness information is present in the response.
     *
     * When the responses TTL is <= 0, the response may not be served from cache without first
     * revalidating with the origin.
     *
     * @return integer|null The TTL in seconds
     *
     * @api
     */
    public function getTtl()
    {
        if (null !== $maxAge = $this->getMaxAge()) {
            return $maxAge - $this->getAge();
        }

        return null;
    }

    /**
     * Sets the response's time-to-live for shared caches.
     *
     * This method adjusts the Cache-Control/s-maxage directive.
     *
     * @param integer $seconds Number of seconds
     *
     * @return Response
     *
     * @api
     */
    public function setTtl($seconds)
    {
        $this->setSharedMaxAge($this->getAge() + $seconds);

        return $this;
    }

    /**
     * Sets the response's time-to-live for private/client caches.
     *
     * This method adjusts the Cache-Control/max-age directive.
     *
     * @param integer $seconds Number of seconds
     *
     * @return Response
     *
     * @api
     */
    public function setClientTtl($seconds)
    {
        $this->setMaxAge($this->getAge() + $seconds);

        return $this;
    }

    /**
     * Returns the literal value of the ETag HTTP header.
     *
     * @return string|null The ETag HTTP header or null if it does not exist
     */
    public function getEtag()
    {
        return $this->headers->getItem('ETag');
    }

    /**
     * Sets the ETag value.
     *
     * @param string|null $etag The ETag unique identifier or null to remove the header
     * @param Boolean     $weak Whether you want a weak ETag or not
     * @return Response
     */
    public function setEtag($etag = null, $weak = false)
    {
        if (null === $etag) {
            $this->headers->remove('Etag');
        } else {
            if (0 !== strpos($etag, '"')) {
                $etag = '"' . $etag . '"';
            }

            $this->headers->set('ETag', (true === $weak ? 'W/' : '') . $etag);
        }

        return $this;
    }

    /**
     * Returns a DateTime object initialized at the $time param and using UTC
     * as timezone
     *
     * @param string|int|DateTime $time 
     * @return DateTime
     */
    protected function _getUTCDate($time = null)
    {
        if ($time instanceof DateTime) {
            $result = clone $time;
        } elseif (is_integer($time)) {
            $result = new DateTime(date('Y-m-d H:i:s', $time));
        } else {
            $result = new DateTime($time);
        }
        $result->setTimeZone(new DateTimeZone('UTC'));
        return $result;
    }

    /**
     * Returns the Response as an HTTP string.
     *
     * The string representation of the Response is the same as the
     * one that will be sent to the client only if the prepare() method
     * has been called before.
     *
     * @return string The Response as an HTTP string
     *
     * @see prepare()
     */
    public function __toString()
    {
        return
                sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText) . "\r\n" .
                $this->headers . "\r\n" .
                $this->getContent();
    }

    /**
     * Returns true if the response is "fresh".
     *
     * Fresh responses may be served from cache without any interaction with the
     * origin. A response is considered fresh when it includes a Cache-Control/max-age
     * indicator or Expires header and the calculated age is less than the freshness lifetime.
     *
     * @return Boolean true if the response is fresh, false otherwise
     *
     * @api
     */
    public function isFresh()
    {
        return $this->getTtl() > 0;
    }

    /**
     * Returns true if the response includes headers that can be used to validate
     * the response with the origin server using a conditional GET request.
     *
     * @return Boolean true if the response is validateable, false otherwise
     *
     * @api
     */
    public function isValidateable()
    {
        return $this->headers->contains('Last-Modified') || $this->headers->contains('ETag');
    }

    /**
     * Marks the response as "private".
     *
     * It makes the response ineligible for serving other clients.
     *
     * @return Response
     *
     * @api
     */
    public function setPrivate()
    {
        $this->headers->removeCacheControlDirective('public');
        $this->headers->addCacheControlDirective('private');

        return $this;
    }

    /**
     * Marks the response as "public".
     *
     * It makes the response eligible for serving other clients.
     *
     * @return Response
     *
     * @api
     */
    public function setPublic()
    {
        $this->headers->addCacheControlDirective('public');
        $this->headers->removeCacheControlDirective('private');

        return $this;
    }

    /**
     * Returns true if the response must be revalidated by caches.
     *
     * This method indicates that the response must not be served stale by a
     * cache in any circumstance without first revalidating with the origin.
     * When present, the TTL of the response should not be overridden to be
     * greater than the value provided by the origin.
     *
     * @return Boolean true if the response must be revalidated by a cache, false otherwise
     *
     * @api
     */
    public function mustRevalidate()
    {
        return $this->headers->hasCacheControlDirective('must-revalidate') || $this->headers->contains('proxy-revalidate');
    }

    /**
     * Is response invalid?
     *
     * @return Boolean
     */
    public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Is response informative?
     *
     * @return Boolean
     *
     * @api
     */
    public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * Is response successful?
     *
     * @return Boolean
     *
     * @api
     */
    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Is the response a redirect?
     *
     * @return Boolean
     *
     * @api
     */
    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Is there a client error?
     *
     * @return Boolean
     *
     * @api
     */
    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Was there a server side error?
     *
     * @return Boolean
     *
     * @api
     */
    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Is the response OK?
     *
     * @return Boolean
     *
     * @api
     */
    public function isOk()
    {
        return 200 === $this->statusCode;
    }

    /**
     * Is the response forbidden?
     *
     * @return Boolean
     *
     * @api
     */
    public function isForbidden()
    {
        return 403 === $this->statusCode;
    }

    /**
     * Is the response a not found error?
     *
     * @return Boolean
     *
     * @api
     */
    public function isNotFound()
    {
        return 404 === $this->statusCode;
    }

    /**
     * Is the response a redirect of some form?
     *
     * @param string $location
     *
     * @return Boolean
     *
     * @api
     */
    public function isRedirect($location = null)
    {
        return in_array($this->statusCode, array(201, 301, 302, 303, 307, 308)) && (null === $location ? : $location == $this->headers->getItem('Location'));
    }

    /**
     * Is the response empty?
     *
     * @return Boolean
     *
     * @api
     */
    public function isEmpty()
    {
        return in_array($this->statusCode, array(201, 204, 304));
    }

}
