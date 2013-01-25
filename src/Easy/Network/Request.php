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

use ArrayAccess;
use Easy\Network\Exception\MethodNotAllowedException;
use Easy\Storage\Session\SessionInterface;
use Easy\Utility\Hash;
use InvalidArgumentException;
use Locale;
use LogicException;
use UnexpectedValueException;

/**
 * Request represents an HTTP request.
 *
 * The methods dealing with URL accept / return a raw path (% encoded):
 *   * getBasePath
 *   * getBaseUrl
 *   * getPathInfo
 *   * getRequestUri
 *   * getUri
 *   * getUriForPath
 *
 * @author Ítalo Lelis de Vietro <italolelis@gmail.com>
 */
class Request implements ArrayAccess
{

    const HEADER_CLIENT_IP = 'client_ip';
    const HEADER_CLIENT_HOST = 'client_host';
    const HEADER_CLIENT_PROTO = 'client_proto';
    const HEADER_CLIENT_PORT = 'client_port';

    protected static $trustProxy = false;
    protected static $trustedProxies = array();

    /**
     * Names for headers that can be trusted when
     * using trusted proxies.
     *
     * The default names are non-standard, but widely used
     * by popular reverse proxies (like Apache mod_proxy or Amazon EC2).
     */
    protected static $trustedHeaders = array(
        self::HEADER_CLIENT_IP => 'X_FORWARDED_FOR',
        self::HEADER_CLIENT_HOST => 'X_FORWARDED_HOST',
        self::HEADER_CLIENT_PROTO => 'X_FORWARDED_PROTO',
        self::HEADER_CLIENT_PORT => 'X_FORWARDED_PORT',
    );
    protected static $httpMethodParameterOverride = false;

    /**
     * @var array
     */
    public $params = array(
        'plugin' => null,
        'controller' => null,
        'action' => null,
        'pass' => array(),
    );

    /**
     * @var array
     */
    public $data = array();

    /**
     * @var array
     */
    protected $_detectors = array(
        'get' => array('env' => 'REQUEST_METHOD', 'value' => 'GET'),
        'post' => array('env' => 'REQUEST_METHOD', 'value' => 'POST'),
        'put' => array('env' => 'REQUEST_METHOD', 'value' => 'PUT'),
        'delete' => array('env' => 'REQUEST_METHOD', 'value' => 'DELETE'),
        'head' => array('env' => 'REQUEST_METHOD', 'value' => 'HEAD'),
        'options' => array('env' => 'REQUEST_METHOD', 'value' => 'OPTIONS'),
        'ssl' => array('env' => 'HTTPS', 'value' => 1),
        'ajax' => array('env' => 'HTTP_X_REQUESTED_WITH', 'value' => 'XMLHttpRequest'),
        'flash' => array('env' => 'HTTP_USER_AGENT', 'pattern' => '/^(Shockwave|Adobe) Flash/'),
        'mobile' => array('env' => 'HTTP_USER_AGENT', 'options' => array(
                'Android', 'AvantGo', 'BlackBerry', 'DoCoMo', 'Fennec', 'iPod', 'iPhone', 'iPad',
                'J2ME', 'MIDP', 'NetFront', 'Nokia', 'Opera Mini', 'Opera Mobi', 'PalmOS', 'PalmSource',
                'portalmmm', 'Plucker', 'ReqwirelessWeb', 'SonyEricsson', 'Symbian', 'UP\\.Browser',
                'webOS', 'Windows CE', 'Windows Phone OS', 'Xiino'
        )),
        'requested' => array('param' => 'requested', 'value' => 1)
    );

    /**
     * @var string
     */
    protected $content;

    /**
     * @var array
     */
    protected $languages;

    /**
     * @var ServerBag
     */
    public $server;

    /**
     * @var HeaderBag
     */
    public $headers;

    /**
     * @var ParameterBag
     */
    public $attributes;

    /**
     * @var ParameterBag
     */
    public $query = array();

    /**
     * @var ParameterBag
     */
    public $cookies;

    /**
     * @var FileBag
     */
    public $files;

    /**
     * @var string
     */
    protected $requestUri;

    /**
     * @var array
     */
    protected $charsets;

    /**
     * @var array
     */
    protected $acceptableContentTypes;

    /**
     * @var string
     */
    protected $pathInfo;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $defaultLocale = 'en';

    /**
     * @var array
     */
    protected static $formats;

    public static function createFromGlobals()
    {
        $request = new static($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
        if ($request->headers->contains('CONTENT_TYPE')) {
            if (0 === strpos($request->headers->getItem('CONTENT_TYPE'), 'application/x-www-form-urlencoded') && in_array(strtoupper($request->server->getItem('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
            ) {
                parse_str($request->getContent(), $data);
                $request->data = $data;
            }
        }

        return $request;
    }

    /**
     * Constructor.
     *
     * @param array  $query      The GET parameters
     * @param array  $request    The POST parameters
     * @param array  $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array  $cookies    The COOKIE parameters
     * @param array  $files      The FILES parameters
     * @param array  $server     The SERVER parameters
     * @param string $content    The raw body data
     */
    public function __construct(array $query = array(), array $request = array(), $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array  $query      The GET parameters
     * @param array  $request    The POST parameters
     * @param array  $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array  $cookies    The COOKIE parameters
     * @param array  $files      The FILES parameters
     * @param array  $server     The SERVER parameters
     * @param string $content    The raw body data
     */
    public function initialize(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->server = new ServerBag($server);
        $this->headers = new HeaderBag($this->server->getHeaders());
        $this->data = $request;
        $this->query = new ParameterBag($query);
        $this->attributes = new ParameterBag($attributes);
        $this->cookies = new ParameterBag($cookies);
        $this->files = new FileBag($files);

        $this->content = $content;
        $this->languages = null;
        $this->charsets = null;
        $this->acceptableContentTypes = null;
        $this->pathInfo = null;
        $this->requestUri = null;
        $this->baseUrl = null;
        $this->basePath = null;
        $this->method = null;
        $this->format = null;
    }

    /**
     * Process the GET parameters and move things into the object.
     *
     * @return void
     */
    protected function _processGet($query)
    {
        $url = $this->getRequestUrl();
        unset($query['/' . str_replace('.', '_', urldecode($url))]);
        if (strpos($url, '?') !== false) {
            list(, $querystr) = explode('?', $url);
            parse_str($querystr, $queryArgs);
            $query += $queryArgs;
        }
        return $query;
    }

    /**
     * Get the request uri.  Looks in PATH_INFO first, as this is the exact value we need prepared
     * by PHP.  Following that, REQUEST_URI, PHP_SELF, HTTP_X_REWRITE_URL and argv are checked in that order.
     * Each of these server variables have the base path, and query strings stripped off
     *
     * @return string URI The EasyFw request path that is being accessed.
     */
    public function getRequestUrl()
    {
        $uri = $this->getRequestUri();
        $base = $this->getBaseUrl();

        if (strlen($base) > 0 && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        if (strpos($uri, '?') !== false) {
            list($uri) = explode('?', $uri, 2);
        }
        if (empty($uri) || $uri == '/' || $uri == '//') {
            return '/';
        }
        return $uri;
    }

    /**
     * Sets a list of trusted proxies.
     *
     * You should only list the reverse proxies that you manage directly.
     *
     * @param array $proxies A list of trusted proxies
     *
     * @api
     */
    public static function setTrustedProxies(array $proxies)
    {
        self::$trustedProxies = $proxies;
        self::$trustProxy = $proxies ? true : false;
    }

    /**
     * Gets the list of trusted proxies.
     *
     * @return array An array of trusted proxies.
     */
    public static function getTrustedProxies()
    {
        return self::$trustedProxies;
    }

    /**
     * Sets the name for trusted headers.
     *
     * The following header keys are supported:
     *
     *  * Request::HEADER_CLIENT_IP:    defaults to X-Forwarded-For   (see getClientIp())
     *  * Request::HEADER_CLIENT_HOST:  defaults to X-Forwarded-Host  (see getClientHost())
     *  * Request::HEADER_CLIENT_PORT:  defaults to X-Forwarded-Port  (see getClientPort())
     *  * Request::HEADER_CLIENT_PROTO: defaults to X-Forwarded-Proto (see getScheme() and isSecure())
     *
     * Setting an empty value allows to disable the trusted header for the given key.
     *
     * @param string $key   The header key
     * @param string $value The header name
     *
     * @throws InvalidArgumentException
     */
    public static function setTrustedHeaderName($key, $value)
    {
        if (!array_key_exists($key, self::$trustedHeaders)) {
            throw new InvalidArgumentException(sprintf('Unable to set the trusted header name for key "%s".', $key));
        }

        self::$trustedHeaders[$key] = $value;
    }

    /**
     * Enables support for the _method request parameter to determine the intended HTTP method.
     *
     * Be warned that enabling this feature might lead to CSRF issues in your code.
     * Check that you are using CSRF tokens when required.
     *
     * The HTTP method can only be overridden when the real HTTP method is POST.
     */
    public static function enableHttpMethodParameterOverride()
    {
        self::$httpMethodParameterOverride = true;
    }

    /**
     * Gets a "parameter" value.
     *
     * This method is mainly useful for libraries that want to provide some flexibility.
     *
     * Order of precedence: GET, PATH, POST
     *
     * Avoid using this method in controllers:
     *
     *  * slow
     *  * prefer to get from a "named" source
     *
     * It is better to explicitly get request parameters from the appropriate
     * public property instead (query, attributes, request).
     *
     * @param string  $key     the key
     * @param mixed   $default the default value
     * @param Boolean $deep    is parameter deep in multidimensional array
     *
     * @return mixed
     */
    public function get($key, $default = null, $deep = false)
    {
        $value = null;
        if (isset($this->request[$key])) {
            $value = $this->request[$key];
        } elseif ($this->attributes->contains($key)) {
            $value = $this->attributes->getItem($default);
        } elseif ($this->query->contains($key)) {
            $value = $this->query->getItem($key);
        }
        return $value;
    }

    /**
     * Gets the Session.
     *
     * @return SessionInterface|null The session
     *
     * @api
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Whether the request contains a Session which was started in one of the
     * previous requests.
     *
     * @return Boolean
     *
     * @api
     */
    public function hasPreviousSession()
    {
        // the check for $this->session avoids malicious users trying to fake a session cookie with proper name
        return $this->hasSession() && $this->cookies->has($this->session->getName());
    }

    /**
     * Whether the request contains a Session object.
     *
     * This method does not give any information about the state of the session object,
     * like whether the session is started or not. It is just a way to check if this Request
     * is associated with a Session instance.
     *
     * @return Boolean true when the Request contains a Session object, false otherwise
     *
     * @api
     */
    public function hasSession()
    {
        return null !== $this->session;
    }

    /**
     * Sets the Session.
     *
     * @param SessionInterface $session The Session
     *
     * @api
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Returns the client IP address.
     *
     * This method can read the client IP address from the "X-Forwarded-For" header
     * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
     * header value is a comma+space separated list of IP addresses, the left-most
     * being the original client, and each successive proxy that passed the request
     * adding the IP address where it received the request from.
     *
     * If your reverse proxy uses a different header name than "X-Forwarded-For",
     * ("Client-Ip" for instance), configure it via "setTrustedHeaderName()" with
     * the "client-ip" key.
     *
     * @return string The client IP address
     *
     * @see http://en.wikipedia.org/wiki/X-Forwarded-For
     */
    public function getClientIp()
    {
        $ip = $this->server->get('REMOTE_ADDR');

        if (!self::$trustProxy) {
            return $ip;
        }

        if (!self::$trustedHeaders[self::HEADER_CLIENT_IP] || !$this->headers->has(self::$trustedHeaders[self::HEADER_CLIENT_IP])) {
            return $ip;
        }

        $clientIps = array_map('trim', explode(',', $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_IP])));
        $clientIps[] = $ip;

        $trustedProxies = self::$trustProxy && !self::$trustedProxies ? array($ip) : self::$trustedProxies;
        $clientIps = array_diff($clientIps, $trustedProxies);

        return array_pop($clientIps);
    }

    /**
     * Returns current script name.
     *
     * @return string
     *
     * @api
     */
    public function getScriptName()
    {
        return $this->server->getItem('SCRIPT_NAME', $this->server->getItem('ORIG_SCRIPT_NAME', ''));
    }

    /**
     * Returns the request body content.
     *
     * @param Boolean $asResource If true, a resource will be returned
     *
     * @return string|resource The request body content or a resource to read the body stream.
     *
     * @throws LogicException
     */
    public function getContent($asResource = false)
    {
        if (false === $this->content || (true === $asResource && null !== $this->content)) {
            throw new LogicException('getContent() can only be called once when using the resource return type.');
        }

        if (true === $asResource) {
            $this->content = false;

            return fopen('php://input', 'rb');
        }

        if (null === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * Returns the referer that referred this request.
     *
     * @param boolean $local Attempt to return a local address. Local addresses do not contain hostnames.
     * @return string The referring address for this request.
     */
    public function referer($local = false)
    {
        $ref = env('HTTP_REFERER');
        if ($this->trustProxy && env('HTTP_X_FORWARDED_HOST')) {
            $ref = env('HTTP_X_FORWARDED_HOST');
        }

        $base = '';
        if (defined('FULL_BASE_URL')) {
            $base = FULL_BASE_URL . $this->webroot;
        }
        if (!empty($ref) && !empty($base)) {
            if ($local && strpos($ref, $base) === 0) {
                $ref = substr($ref, strlen($base));
                if ($ref[0] != '/') {
                    $ref = '/' . $ref;
                }
                return $ref;
            } elseif (!$local) {
                return $ref;
            }
        }
        return '/';
    }

    /**
     * Checks whether the method is safe or not.
     *
     * @return Boolean
     *
     * @api
     */
    public function isMethodSafe()
    {
        return in_array($this->getMethod(), array('GET', 'HEAD'));
    }

    /**
     * Magic get method allows access to parsed routing parameters directly on the object.
     *
     * Allows access to `$this->params['controller']` via `$this->controller`
     *
     * @param string $name The property being accessed.
     * @return mixed Either the value of the parameter or null.
     */
    public function __get($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return null;
    }

    /**
     * Magic isset method allows isset/empty checks
     * on routing parameters.
     *
     * @param string $name The property being accessed.
     * @return bool Existence
     */
    public function __isset($name)
    {
        return isset($this->params[$name]);
    }

    /**
     * Check whether or not a Request is a certain type.  Uses the built in detection rules
     * as well as additional rules defined with Request::addDetector().  Any detector can be called
     * as `is($type)` or `is$Type()`.
     *
     * @param string $type The type of request you want to check.
     * @return boolean Whether or not the request is the type you are checking.
     */
    public function is($type)
    {
        $type = strtolower($type);
        if (!isset($this->_detectors[$type])) {
            return false;
        }
        $detect = $this->_detectors[$type];
        if (isset($detect['env'])) {
            if (isset($detect['value'])) {
                if ($this->server->contains($detect['env'])) {
                    return $this->server->getItem($detect['env']) == $detect['value'];
                }
            }
            if (isset($detect['pattern'])) {
                return (bool) preg_match($detect['pattern'], env($detect['env']));
            }
            if (isset($detect['options'])) {
                $pattern = '/' . implode('|', $detect['options']) . '/i';
                return (bool) preg_match($pattern, env($detect['env']));
            }
        }
        if (isset($detect['param'])) {
            $key = $detect['param'];
            $value = $detect['value'];
            return isset($this->params[$key]) ? $this->params[$key] == $value : false;
        }
        if (isset($detect['callback']) && is_callable($detect['callback'])) {
            return call_user_func($detect['callback'], $this);
        }
        return false;
    }

    /**
     * Gets the "real" request method.
     *
     * @return string The request method
     *
     * @see getMethod
     */
    public function getRealMethod()
    {
        return strtoupper($this->server->getItem('REQUEST_METHOD', 'GET'));
    }

    /**
     * Gets the mime type associated with the format.
     *
     * @param string $format The format
     *
     * @return string The associated mime type (null if not found)
     *
     * @api
     */
    public function getMimeType($format)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }

        return isset(static::$formats[$format]) ? static::$formats[$format][0] : null;
    }

    /**
     * Gets the format associated with the mime type.
     *
     * @param string $mimeType The associated mime type
     *
     * @return string|null The format (null if not found)
     *
     * @api
     */
    public function getFormat($mimeType)
    {
        if (false !== $pos = strpos($mimeType, ';')) {
            $mimeType = substr($mimeType, 0, $pos);
        }

        if (null === static::$formats) {
            static::initializeFormats();
        }

        foreach (static::$formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
        }
        return null;
    }

    /**
     * Associates a format with mime types.
     *
     * @param string       $format    The format
     * @param string|array $mimeTypes The associated mime types (the preferred one must be the first as it will be used as the content type)
     *
     * @api
     */
    public function setFormat($format, $mimeTypes)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }

        static::$formats[$format] = is_array($mimeTypes) ? $mimeTypes : array($mimeTypes);
    }

    /**
     * Gets the request format.
     *
     * Here is the process to determine the format:
     *
     *  * format defined by the user (with setRequestFormat())
     *  * _format request parameter
     *  * $default
     *
     * @param string $default The default format
     *
     * @return string The request format
     *
     * @api
     */
    public function getRequestFormat($default = 'html')
    {
        if (null === $this->format) {
            $this->format = $this->get('_format', $default);
        }

        return $this->format;
    }

    /**
     * Gets the format associated with the request.
     *
     * @return string|null The format (null if no content type is present)
     *
     * @api
     */
    public function getContentType()
    {
        return $this->getFormat($this->headers->getItem('CONTENT_TYPE'));
    }

    /**
     * Sets the default locale.
     *
     * @param string $locale
     *
     * @api
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;

        if (null === $this->locale) {
            $this->setPhpDefaultLocale($locale);
        }
    }

    /**
     * Sets the locale.
     *
     * @param string $locale
     *
     * @api
     */
    public function setLocale($locale)
    {
        $this->setPhpDefaultLocale($this->locale = $locale);
    }

    /**
     * Get the locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return null === $this->locale ? $this->defaultLocale : $this->locale;
    }

    /**
     * Sets the request format.
     *
     * @param string $format The request format.
     *
     * @api
     */
    public function setRequestFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc).
     *
     * @return Boolean
     */
    public function isMethod($method)
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * Sets the request method.
     *
     * @param string $method
     *
     * @api
     */
    public function setMethod($method)
    {
        $this->method = null;
        $this->server->set('REQUEST_METHOD', $method);
    }

    /**
     * Gets the request "intended" method.
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP method,
     * but only if enableHttpMethodParameterOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @return string The request method
     *
     * @api
     *
     * @see getRealMethod
     */
    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = strtoupper($this->server->getItem('REQUEST_METHOD', 'GET'));

            if ('POST' === $this->method) {
                if ($this->headers->contains('X-HTTP-METHOD-OVERRIDE')) {
                    $this->method = strtoupper($this->headers->getItem('X-HTTP-METHOD-OVERRIDE'));
                } elseif (self::$httpMethodParameterOverride) {
                    $this->method = strtoupper($this->request['_method'] = $this->query->getItem('_method', 'POST'));
                }
            }
        }

        return $this->method;
    }

    /**
     * Add parameters to the request's parsed parameter set. This will overwrite any existing parameters.
     * This modifies the parameters available through `$request->params`.
     *
     * @param array $params Array of parameters to merge in
     * @return The current object, you can chain this method.
     */
    public function addParams($params)
    {
        $this->params = array_merge($this->params, (array) $params);
        return $this;
    }

    /**
     * Find out which content types the client accepts or check if they accept a
     * particular type of content.
     *
     * #### Get all types:
     *
     * `$this->request->accepts();`
     *
     * #### Check for a single type:
     *
     * `$this->request->accepts('application/json');`
     *
     * This method will order the returned content types by the preference values indicated
     * by the client.
     *
     * @param string $type The content type to check for.  Leave null to get all types a client accepts.
     * @return mixed Either an array of all the types the client accepts or a boolean if they accept the
     *   provided type.
     */
    public function accepts($type = null)
    {
        $accepts = explode(",", $this->headers->getItem('Accept'));
        foreach ($accepts as $accept) {
            if ($this->getFormat($accept) === $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the Etags.
     *
     * @return array The entity tags
     */
    public function getETags()
    {
        if ($this->headers->contains('if_none_match')) {
            return preg_split('/\s*,\s*/', $this->headers->getItem('if_none_match'), null, PREG_SPLIT_NO_EMPTY);
        }
        return null;
    }

    /**
     * Returns the preferred language.
     *
     * @param array $locales An array of ordered available locales
     *
     * @return string|null The preferred locale
     *
     * @api
     */
    public function getPreferredLanguage(array $locales = null)
    {
        $preferredLanguages = $this->getLanguages();

        if (empty($locales)) {
            return isset($preferredLanguages[0]) ? $preferredLanguages[0] : null;
        }

        if (!$preferredLanguages) {
            return $locales[0];
        }

        $preferredLanguages = array_values(array_intersect($preferredLanguages, $locales));

        return isset($preferredLanguages[0]) ? $preferredLanguages[0] : $locales[0];
    }

    /**
     * Gets a list of languages acceptable by the client browser.
     *
     * @return array Languages ordered in the user browser preferences
     *
     * @api
     */
    public function getLanguages()
    {
        if (null !== $this->languages) {
            return $this->languages;
        }

        $languages = AcceptHeader::fromString($this->headers->getItem('Accept-Language'))->all();
        $this->languages = array();
        foreach (array_keys($languages) as $lang) {
            if (strstr($lang, '-')) {
                $codes = explode('-', $lang);
                if ($codes[0] == 'i') {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registered with the
                    // i-prefix, such as i-cherokee
                    if (count($codes) > 1) {
                        $lang = $codes[1];
                    }
                } else {
                    for ($i = 0, $max = count($codes); $i < $max; $i++) {
                        if ($i == 0) {
                            $lang = strtolower($codes[0]);
                        } else {
                            $lang .= '_' . strtoupper($codes[$i]);
                        }
                    }
                }
            }

            $this->languages[] = $lang;
        }

        return $this->languages;
    }

    /**
     * Gets a list of charsets acceptable by the client browser.
     *
     * @return array List of charsets in preferable order
     *
     * @api
     */
    public function getCharsets()
    {
        if (null !== $this->charsets) {
            return $this->charsets;
        }

        return $this->charsets = array_keys(AcceptHeader::fromString($this->headers->getItem('Accept-Charset'))->all());
    }

    /**
     * Gets a list of content types acceptable by the client browser
     *
     * @return array List of content types in preferable order
     *
     * @api
     */
    public function getAcceptableContentTypes()
    {
        if (null !== $this->acceptableContentTypes) {
            return $this->acceptableContentTypes;
        }
        return $this->acceptableContentTypes = array_keys(AcceptHeader::fromString($this->headers->getItem('Accept'))->all());
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * It works if your JavaScript library set an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     * @link http://en.wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return Boolean true if the request is an XMLHttpRequest, false otherwise
     *
     * @api
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->headers->get('X-Requested-With');
    }

    /**
     * Sets the default PHP locale.
     *
     * @param string $locale
     */
    private function setPhpDefaultLocale($locale)
    {
        // if either the class Locale doesn't exist, or an exception is thrown when
        // setting the default locale, the intl module is not installed, and
        // the call can be ignored:
        try {
            if (class_exists('Locale', false)) {
                Locale::setDefault($locale);
            }
        } catch (\Exception $e) {
            
        }
    }

    /**
     * Provides a read/write accessor for `$this->data`.  Allows you
     * to use a syntax similar to `Session` for reading post data.
     *
     * ## Reading values.
     *
     * `$request->data('Post.title');`
     *
     * When reading values you will get `null` for keys/values that do not exist.
     *
     * ## Writing values
     *
     * `$request->data('Post.title', 'New post!');`
     *
     * You can write to any value, even paths/keys that do not exist, and the arrays
     * will be created for you.
     *
     * @param string $name,... Dot separated name of the value to read/write
     * @return mixed Either the value being read, or this so you can chain consecutive writes.
     */
    public function data($name)
    {
        $args = func_get_args();
        if (count($args) == 2) {
            $this->data = Hash::insert($this->data, $name, $args[1]);
            return $this;
        }
        return Hash::get($this->data, $name);
    }

    /*
     * Only allow certain HTTP request methods, if the request method does not match
     * a 405 error will be shown and the required "Allow" response header will be set.
     *
     * Example:
     *
     * $this->request->onlyAllow('post', 'delete');
     * or
     * $this->request->onlyAllow(array('post', 'delete'));
     *
     * If the request would be GET, response header "Allow: POST, DELETE" will be set
     * and a 405 error will be returned
     *
     * @param string|array $methods Allowed HTTP request methods
     * @return boolean true
     * @throws MethodNotAllowedException
     */

    public function onlyAllow($methods)
    {
        if (!is_array($methods)) {
            $methods = func_get_args();
        }
        foreach ($methods as $method) {
            if ($this->is($method)) {
                return true;
            }
        }
        $allowed = strtoupper(implode(', ', $methods));
        $e = new MethodNotAllowedException();
        $e->responseHeader('Allow', $allowed);
        throw $e;
    }

    /**
     * Array access read implementation
     *
     * @param string $name Name of the key being accessed.
     * @return mixed
     */
    public function offsetGet($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        if ($name == 'url') {
            return $this->query;
        }
        if ($name == 'data') {
            return $this->data;
        }
        return null;
    }

    /**
     * Array access write implementation
     *
     * @param string $name Name of the key being written
     * @param mixed $value The value being written.
     * @return void
     */
    public function offsetSet($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Array access isset() implementation
     *
     * @param string $name thing to check.
     * @return boolean
     */
    public function offsetExists($name)
    {
        return isset($this->params[$name]);
    }

    /**
     * Array access unset() implementation
     *
     * @param string $name Name to unset.
     * @return void
     */
    public function offsetUnset($name)
    {
        unset($this->params[$name]);
    }

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string The raw path (i.e. not urldecoded)
     *
     * @api
     */
    public function getPathInfo()
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    /**
     * Returns the root path from which this request is executed.
     *
     * Suppose that an index.php file instantiates this request object:
     *
     *  * http://localhost/index.php         returns an empty string
     *  * http://localhost/index.php/page    returns an empty string
     *  * http://localhost/web/index.php     returns '/web'
     *  * http://localhost/we%20b/index.php  returns '/we%20b'
     *
     * @return string The raw path (i.e. not urldecoded)
     *
     * @api
     */
    public function getBasePath()
    {
        if (null === $this->basePath) {
            $this->basePath = $this->prepareBasePath();
        }

        return $this->basePath;
    }

    /**
     * Returns the root url from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getBasePath(), except that it also includes the
     * script filename (e.g. index.php) if one exists.
     *
     * @return string The raw url (i.e. not urldecoded)
     *
     * @api
     */
    public function getBaseUrl()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    /**
     * Gets the request's scheme.
     *
     * @return string
     *
     * @api
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Port" header must contain the client port.
     *
     * If your reverse proxy uses a different header name than "X-Forwarded-Port",
     * configure it via "setTrustedHeaderName()" with the "client-port" key.
     *
     * @return string
     *
     * @api
     */
    public function getPort()
    {
        if (self::$trustProxy && self::$trustedHeaders[self::HEADER_CLIENT_PORT] && $port = $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_PORT])) {
            return $port;
        }

        return $this->server->getItem('SERVER_PORT');
    }

    /**
     * Returns the user.
     *
     * @return string|null
     */
    public function getUser()
    {
        return $this->server->getItem('PHP_AUTH_USER');
    }

    /**
     * Returns the password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->server->getItem('PHP_AUTH_PW');
    }

    /**
     * Gets the user info.
     *
     * @return string A user name and, optionally, scheme-specific information about how to gain authorization to access the server
     */
    public function getUserInfo()
    {
        $userinfo = $this->getUser();

        $pass = $this->getPassword();
        if ('' != $pass) {
            $userinfo .= ":$pass";
        }

        return $userinfo;
    }

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     *
     * @return string
     *
     * @api
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }

    /**
     * Returns the requested URI.
     *
     * @return string The raw URI (i.e. not urldecoded)
     *
     * @api
     */
    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Gets the scheme and HTTP host.
     *
     * If the URL was called with basic authentication, the user
     * and the password are not added to the generated string.
     *
     * @return string The scheme and HTTP host
     */
    public function getSchemeAndHttpHost()
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    /**
     * Generates a normalized URI for the Request.
     *
     * @return string A normalized URI for the Request
     *
     * @see getQueryString()
     *
     * @api
     */
    public function getUri()
    {
        if (null !== $qs = $this->getQueryString()) {
            $qs = '?' . $qs;
        }

        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $this->getPathInfo() . $qs;
    }

    /**
     * Generates a normalized URI for the given path.
     *
     * @param string $path A path to use instead of the current one
     *
     * @return string The normalized URI for the path
     *
     * @api
     */
    public function getUriForPath($path)
    {
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $path;
    }

    /**
     * Checks whether the request is secure or not.
     *
     * This method can read the client port from the "X-Forwarded-Proto" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
     *
     * If your reverse proxy uses a different header name than "X-Forwarded-Proto"
     * ("SSL_HTTPS" for instance), configure it via "setTrustedHeaderName()" with
     * the "client-proto" key.
     *
     * @return Boolean
     *
     * @api
     */
    public function isSecure()
    {
        if (self::$trustProxy && self::$trustedHeaders[self::HEADER_CLIENT_PROTO] && $proto = $this->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_PROTO])) {
            return in_array(strtolower($proto), array('https', 'on', '1'));
        }
        if (!$this->server->contains('HTTPS')) {
            return false;
        }
        return 'on' == strtolower($this->server->getItem('HTTPS')) || 1 == $this->server->getItem('HTTPS');
    }

    /**
     * Returns the host name.
     *
     * This method can read the client port from the "X-Forwarded-Host" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Host" header must contain the client host name.
     *
     * If your reverse proxy uses a different header name than "X-Forwarded-Host",
     * configure it via "setTrustedHeaderName()" with the "client-host" key.
     *
     * @return string
     *
     * @throws UnexpectedValueException when the host name is invalid
     *
     * @api
     */
    public function getHost()
    {
        if (self::$trustProxy && self::$trustedHeaders[self::HEADER_CLIENT_HOST] && $host = $this->headers->getItem(self::$trustedHeaders[self::HEADER_CLIENT_HOST])) {
            $elements = explode(',', $host);

            $host = $elements[count($elements) - 1];
        } elseif (!$host = $this->headers->getItem('HOST')) {
            if (!$host = $this->server->getItem('SERVER_NAME')) {
                $host = $this->server->getItem('SERVER_ADDR', '');
            }
        }

        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        // as the host can come from the user (HTTP_HOST and depending on the configuration, SERVER_NAME too can come from the user)
        // check that it does not contain forbidden characters (see RFC 952 and RFC 2181)
        if ($host && !preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host)) {
            throw new UnexpectedValueException('Invalid Host');
        }

        return $host;
    }

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string|null A normalized query string for the Request
     *
     * @api
     */
    public function getQueryString()
    {
        $qs = static::normalizeQueryString($this->server->getItem('QUERY_STRING'));

        return '' === $qs ? null : $qs;
    }

    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     *
     * @param string $qs Query string
     *
     * @return string A normalized query string for the Request
     */
    public static function normalizeQueryString($qs)
    {
        if ('' == $qs) {
            return '';
        }

        $parts = array();
        $order = array();

        foreach (explode('&', $qs) as $param) {
            if ('' === $param || '=' === $param[0]) {
                // Ignore useless delimiters, e.g. "x=y&".
                // Also ignore pairs with empty key, even if there was a value, e.g. "=value", as such nameless values cannot be retrieved anyway.
                // PHP also does not include them when building _GET.
                continue;
            }

            $keyValuePair = explode('=', $param, 2);

            // GET parameters, that are submitted from a HTML form, encode spaces as "+" by default (as defined in enctype application/x-www-form-urlencoded).
            // PHP also converts "+" to spaces when filling the global _GET or when using the function parse_str. This is why we use urldecode and then normalize to
            // RFC 3986 with rawurlencode.
            $parts[] = isset($keyValuePair[1]) ?
                    rawurlencode(urldecode($keyValuePair[0])) . '=' . rawurlencode(urldecode($keyValuePair[1])) :
                    rawurlencode(urldecode($keyValuePair[0]));
            $order[] = urldecode($keyValuePair[0]);
        }

        array_multisort($order, SORT_ASC, $parts);

        return implode('&', $parts);
    }

    protected function prepareRequestUri()
    {
        $requestUri = '';

        if ($this->headers->contains('X_ORIGINAL_URL') && false !== stripos(PHP_OS, 'WIN')) {
            // IIS with Microsoft Rewrite Module
            $requestUri = $this->headers->getItem('X_ORIGINAL_URL');
        } elseif ($this->headers->contains('X_REWRITE_URL') && false !== stripos(PHP_OS, 'WIN')) {
            // IIS with ISAPI_Rewrite
            $requestUri = $this->headers->getItem('X_REWRITE_URL');
        } elseif ($this->server->contains('IIS_WasUrlRewritten') && $this->server->getItem('IIS_WasUrlRewritten') == '1' && $this->server->getItem('UNENCODED_URL') != '') {
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            $requestUri = $this->server->getItem('UNENCODED_URL');
        } elseif ($this->server->contains('REQUEST_URI')) {
            $requestUri = $this->server->getItem('REQUEST_URI');
            // HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
            $schemeAndHttpHost = $this->getSchemeAndHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif ($this->server->contains('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->getItem('ORIG_PATH_INFO');
            if ('' != $this->server->getItem('QUERY_STRING')) {
                $requestUri .= '?' . $this->server->getItem('QUERY_STRING');
            }
        }

        return $requestUri;
    }

    /**
     * Prepares the base URL.
     *
     * @return string
     */
    protected function prepareBaseUrl()
    {
        $filename = basename($this->server->getItem('SCRIPT_FILENAME'));

        //env('PHP_SELF')
        if (basename($this->server->getItem('SCRIPT_NAME')) === $filename) {
            $baseUrl = dirname($this->server->getItem('PHP_SELF'));
            if (basename($baseUrl) === 'public') {
                $baseUrl = dirname($baseUrl);
            }
        } elseif (basename($this->server->getItem('PHP_SELF')) === $filename) {
            $baseUrl = $this->server->getItem('PHP_SELF');
        } elseif (basename($this->server->getItem('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->getItem('ORIG_SCRIPT_NAME'); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $this->server->getItem('PHP_SELF', '');
            $file = $this->server->getItem('SCRIPT_FILENAME', '');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/' . $seg . $baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $prefix;
        }

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/');
        }

        $truncatedRequestUri = $requestUri;
        if (($pos = strpos($requestUri, '?')) !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if ((strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/');
    }

    /**
     * Prepares the base path.
     *
     * @return string base path
     */
    protected function prepareBasePath()
    {
        $filename = basename($this->server->getItem('SCRIPT_FILENAME'));
        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }

        if (basename($baseUrl) === $filename) {
            $basePath = dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }

        if ('\\' === DIRECTORY_SEPARATOR) {
            $basePath = str_replace('\\', '/', $basePath);
        }

        return rtrim($basePath, '/');
    }

    /**
     * Prepares the path info.
     *
     * @return string path info
     */
    protected function preparePathInfo()
    {
        $baseUrl = $this->getBaseUrl();

        if (null === ($requestUri = $this->getRequestUri())) {
            return '/';
        }

        $pathInfo = '/';

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((null !== $baseUrl) && (false === ($pathInfo = substr($requestUri, strlen($baseUrl))))) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }

        return (string) $pathInfo;
    }

    /**
     * Initializes HTTP request formats.
     */
    protected static function initializeFormats()
    {
        static::$formats = array(
            'html' => array('text/html', 'application/xhtml+xml'),
            'txt' => array('text/plain'),
            'js' => array('application/javascript', 'application/x-javascript', 'text/javascript'),
            'css' => array('text/css'),
            'json' => array('application/json', 'application/x-json'),
            'xml' => array('text/xml', 'application/xml', 'application/x-xml'),
            'rdf' => array('application/rdf+xml'),
            'atom' => array('application/atom+xml'),
            'rss' => array('application/rss+xml'),
        );
    }

    /*
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, false otherwise.
     *
     * @param string $string The urlencoded string
     * @param string $prefix The prefix not encoded
     *
     * @return string|false The prefix as it is encoded in $string, or false
     */

    private function getUrlencodedPrefix($string, $prefix)
    {
        if (0 !== strpos(rawurldecode($string), $prefix)) {
            return false;
        }

        $len = strlen($prefix);

        if (preg_match("#^(%[[:xdigit:]]{2}|.){{$len}}#", $string, $match)) {
            return $match[0];
        }

        return false;
    }

}