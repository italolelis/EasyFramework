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
use RuntimeException;

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
     * Buffer list of headers
     *
     * @var array
     */
    protected $headers = array();

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
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->header($headers);
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
//        if (!$headers->has('Content-Type')) {
//            $format = $request->getRequestFormat();
//            if (null !== $format && $mimeType = $request->getMimeType($format)) {
//                $headers->set('Content-Type', $mimeType);
//            }
//        }
        // Fix Content-Type
        if (strpos($this->_contentType, 'text/') === 0) {
            $this->header('Content-Type', "{$this->_contentType}; charset={$this->charset}");
        } else {
            $this->header('Content-Type', "{$this->_contentType}");
        }

        // Fix Content-Length
        if (isset($this->headers['Transfer-Encoding'])) {
            unset($this->headers['Content-Length']);
        }

        if ($request->is('HEAD')) {
            // cf. RFC2616 14.13
            $length = $this->headers['Content-Length'];
            $this->setContent(null);
            if ($length) {
                $this->headers['Content-Length'] = $length;
            }
        }

        // Fix protocol
//        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
//            $this->setProtocolVersion('1.1');
//        }
//
//        // Check if we need to send extra expire info headers
//        if ('1.0' == $this->getProtocolVersion() && 'no-cache' == $this->headers->get('Cache-Control')) {
//            $this->headers->set('pragma', 'no-cache');
//            $this->headers->set('expires', -1);
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

        header(sprintf('HTTP/%s %s %s', $this->protocol, $this->statusCode, $this->statusText));

        foreach ($this->headers as $header => $value) {
            header("{$header}: {$value}");
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
     * Buffers a header string to be sent
     * Returns the complete list of buffered headers
     *
     * ### Single header
     * e.g `header('Location', 'http://example.com');`
     *
     * ### Multiple headers
     * e.g `header(array('Location' => 'http://example.com', 'X-Extra' => 'My header'));`
     *
     * ### String header
     * e.g `header('WWW-Authenticate: Negotiate');`
     *
     * ### Array of string headers
     * e.g `header(array('WWW-Authenticate: Negotiate', 'Content-type: application/pdf'));`
     *
     * Multiple calls for setting the same header name will have the same effect as setting the header once
     * with the last value sent for it
     *  e.g `header('WWW-Authenticate: Negotiate'); header('WWW-Authenticate: Not-Negotiate');`
     * will have the same effect as only doing `header('WWW-Authenticate: Not-Negotiate');`
     *
     * @param mixed $header. An array of header strings or a single header string
     * 	- an assotiative array of "header name" => "header value" is also accepted
     * 	- an array of string headers is also accepted
     * @param mixed $value. The header value.
     * @return array list of headers to be sent
     */
    public function header($header = null, $value = null)
    {
        if (is_null($header)) {
            return $this->headers;
        }
        if (is_array($header)) {
            foreach ($header as $h => $v) {
                if (is_numeric($h)) {
                    $this->header($v);
                    continue;
                }
                $this->headers[$h] = trim($v);
            }
            return $this->headers;
        }

        if (!is_null($value)) {
            $this->headers[$header] = $value;
            return $this->headers;
        }

        list($header, $value) = explode(':', $header, 2);
        $this->headers[$header] = trim($value);
        return $this->headers;
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
     * @throws \UnexpectedValueException
     */
    public function setContent($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array($content, '__toString'))) {
            throw new \UnexpectedValueException('The Response content must be a string or object implementing __toString(), "' . gettype($content) . '" given.');
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
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     *
     * @api
     */
    public function setStatusCode($code, $text = null)
    {

        $this->statusCode = $code = (int) $code;

        if ($this->isInvalid()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
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
        $this->header(array(
            'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
            'Last-Modified' => gmdate("D, d M Y H:i:s") . " GMT",
            'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
        ));
    }

    /**
     * Sets the correct headers to instruct the client to cache the response.
     *
     * @param string $since a valid time since the response text has not been modified
     * @param string $time a valid time for cache expiry
     * @return void
     */
    public function cache($since, $time = '+1 day')
    {
        if (!is_integer($time)) {
            $time = strtotime($time);
        }
        $this->header(array(
            'Date' => gmdate("D, j M Y G:i:s ", time()) . 'GMT'
        ));
        $this->setModified($since);
        $this->setExpires($time);
        $this->sharable(true);
        $this->setMaxAge($time - time());
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
     * Sets the Cache-Control s-maxage directive.
     * The max-age is the number of seconds after which the response should no longer be considered
     * a good candidate to be fetched from a shared cache (like in a proxy server).
     * If called with no parameters, this function will return the current max-age value if any
     *
     * @param int $seconds if null, the method will return the current s-maxage value
     * @return int
     */
    public function setSharedMaxAge($seconds = null)
    {
        if ($seconds !== null) {
            $this->_cacheDirectives['s-maxage'] = $seconds;
            $this->_setCacheControl();
        }
        if (isset($this->_cacheDirectives['s-maxage'])) {
            return $this->_cacheDirectives['s-maxage'];
        }
        return null;
    }

    /**
     * Sets the Cache-Control max-age directive.
     * The max-age is the number of seconds after which the response should no longer be considered
     * a good candidate to be fetched from the local (client) cache.
     * If called with no parameters, this function will return the current max-age value if any
     *
     * @param int $seconds if null, the method will return the current max-age value
     * @return int
     */
    public function setMaxAge($seconds = null)
    {
        if ($seconds !== null) {
            $this->_cacheDirectives['max-age'] = $seconds;
            $this->_setCacheControl();
        }
        if (isset($this->_cacheDirectives['max-age'])) {
            return $this->_cacheDirectives['max-age'];
        }
        return null;
    }

    /**
     * Sets the Expires header for the response by taking an expiration time
     * If called with no parameters it will return the current Expires value
     *
     * ## Examples:
     *
     * `$response->expires('now')` Will Expire the response cache now
     * `$response->expires(new DateTime('+1 day'))` Will set the expiration in next 24 hours
     * `$response->expires()` Will return the current expiration header value
     *
     * @param string|DateTime $time
     * @return string
     */
    public function setExpires($time = null)
    {
        if ($time !== null) {
            $date = $this->_getUTCDate($time);
            $this->headers['Expires'] = $date->format('D, j M Y H:i:s') . ' GMT';
        }
        if (isset($this->headers['Expires'])) {
            return $this->headers['Expires'];
        }
        return null;
    }

    /**
     * Sets the Cache-Control must-revalidate directive.
     * must-revalidate indicates that the response should not be served 
     * stale by a cache under any cirumstance without first revalidating 
     * with the origin.
     * If called with no parameters, this function will return wheter must-revalidate is present.
     *
     * @param int $seconds if null, the method will return the current 
     * must-revalidate value
     * @return boolean
     */
    public function mustRevalidate($enable = null)
    {
        if ($enable !== null) {
            if ($enable) {
                $this->_cacheDirectives['must-revalidate'] = true;
            } else {
                unset($this->_cacheDirectives['must-revalidate']);
            }
            $this->_setCacheControl();
        }
        return array_key_exists('must-revalidate', $this->_cacheDirectives);
    }

    /**
     * Sets the Vary header for the response, if an array is passed,
     * values will be imploded into a comma separated string. If no 
     * parameters are passed, then an array with the current Vary header 
     * value is returned
     *
     * @param string|array $cacheVariances a single Vary string or a array 
     * containig the list for variances.
     * @return array
     * */
    public function setVary($cacheVariances = null)
    {
        if ($cacheVariances !== null) {
            $cacheVariances = (array) $cacheVariances;
            $this->headers['Vary'] = implode(', ', $cacheVariances);
        }
        if (isset($this->headers['Vary'])) {
            return explode(', ', $this->headers['Vary']);
        }
        return null;
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
        $this->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
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
            $this->headers['Content-Length'] = $bytes;
        }
        if (isset($this->headers['Content-Length'])) {
            return $this->headers['Content-Length'];
        }
        return null;
    }

    /**
     * Checks whether a response has not been modified according to the 'If-None-Match' 
     * (Etags) and 'If-Modified-Since' (last modification date) request 
     * headers headers. If the response is detected to be not modified, it 
     * is marked as so accordingly so the client can be informed of that.
     *
     * In order to mark a response as not modified, you need to set at least 
     * the Last-Modified response header or a response etag to be compared 
     * with the request itself
     *
     * @return boolean whether the response was marked as not modified or 
     * not
     * */
    public function isNotModified(Request $request)
    {
        $etags = preg_split('/\s*,\s*/', $request->header('If-None-Match'), null, PREG_SPLIT_NO_EMPTY);
        $modifiedSince = $request->header('If-Modified-Since');
        if ($responseTag = $this->setEtag()) {
            $etagMatches = in_array('*', $etags) || in_array($responseTag, $etags);
        }
        if ($modifiedSince) {
            $timeMatches = strtotime($this->setModified()) == strtotime($modifiedSince);
        }
        $checks = compact('etagMatches', 'timeMatches');
        if (empty($checks)) {
            return false;
        }
        $notModified = !in_array(false, $checks, true);
        if ($notModified) {
            $this->notModified();
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
        $this->header('Cache-Control', $control);
    }

    /**
     * Sets the Last-Modified header for the response by taking an modification time
     * If called with no parameters it will return the current Last-Modified value
     *
     * ## Examples:
     *
     * `$response->modified('now')` Will set the Last-Modified to the current time
     * `$response->modified(new DateTime('+1 day'))` Will set the modification date in the past 24 hours
     * `$response->modified()` Will return the current Last-Modified header value
     *
     * @param string|DateTime $time
     * @return string
     */
    public function setModified($time = null)
    {
        if ($time !== null) {
            $date = $this->_getUTCDate($time);
            $this->headers['Last-Modified'] = $date->format('D, j M Y H:i:s') . ' GMT';
        }
        if (isset($this->headers['Last-Modified'])) {
            return $this->headers['Last-Modified'];
        }
        return null;
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
            unset($this->headers[$header]);
        }
    }

    /**
     * Sets the response Etag, Etags are a strong indicative that a response
     * can be cached by a HTTP client. A bad way of generaing Etags is 
     * creating a hash of the response output, instead generate a unique 
     * hash of the unique components that identifies a request, such as a 
     * modification time, a resource Id, and anything else you consider it 
     * makes it unique.
     *
     * Second parameter is used to instuct clients that the content has 
     * changed, but sematicallly, it can be used as the same thing. Think 
     * for instance of a page with a hit counter, two different page views 
     * are equivalent, but they differ by a few bytes. This leaves off to 
     * the Client the decision of using or not the cached page.
     *
     * If no parameters are passed, current Etag header is returned.
     *
     * @param string $hash the unique has that identifies this resposnse
     * @param boolean $weak whether the response is semantically the same as 
     * other with th same hash or not
     * @return string
     * */
    public function setEtag($tag = null, $weak = false)
    {
        if ($tag !== null) {
            $this->headers['Etag'] = sprintf('%s"%s"', ($weak) ? 'W/' : null, $tag);
        }
        if (isset($this->headers['Etag'])) {
            return $this->headers['Etag'];
        }
        return null;
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
     * String conversion.  Fetches the response body as a string.
     * Does *not* send headers.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->content;
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
        return in_array($this->statusCode, array(201, 301, 302, 303, 307, 308)) && (null === $location ? : $location == $this->headers->get('Location'));
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
