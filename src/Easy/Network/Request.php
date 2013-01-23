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
use Easy\Core\Config;
use Easy\Network\Exception\MethodNotAllowedException;
use Easy\Utility\Hash;
use RuntimeException;

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
    public $query = array();

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $base;

    /**
     * @var string
     */
    public $webroot = '/';

    /**
     * @var string
     */
    public $here;

    /**
     * @var boolean
     */
    public $trustProxy = false;

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
    protected $_input = '';

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $defaultLocale = 'en';

    /**
     * @var ServerBag
     */
    public $server;

    /**
     * @var HeaderBag
     */
    public $headers;

    /**
     * @var string
     */
    protected $requestUri;

    /**
     * Wrapper method to create a new request from PHP superglobals.
     *
     * Uses the $_GET, $_POST, $_FILES, $_COOKIE and php://input data to construct
     * the request.
     *
     * @return Request
     */
    public static function createFromGlobals()
    {
        list($base, $webroot) = static::_base();
        $config = array(
            'query' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
            'cookies' => $_COOKIE,
            'base' => $base,
            'webroot' => $webroot,
        );
        $config['url'] = static::_url($config);
        return new static($config, $_SERVER);
    }

    /**
     * Create a new request object.
     *
     * You can supply the data as either an array or as a string.  If you use
     * a string you can only supply the url for the request.  Using an array will
     * let you provide the following keys:
     *
     * - `post` POST data or non query string data
     * - `query` Additional data from the query string.
     * - `files` Uploaded file data formatted like $_FILES
     * - `cookies` Cookies for this request.
     * - `url` The url without the base path for the request.
     * - `base` The base url for the request.
     * - `webroot` The webroot directory for the request.
     * - `input` The data that would come from php://input this is useful for simulating
     *   requests with put, patch or delete data.
     *
     * @param string|array $config An array of request data to create a request with.
     */
    public function __construct($config = array(), $server = array())
    {
        if (is_string($config)) {
            $config = array('url' => $config);
        }
        $config += array(
            'params' => $this->params,
            'query' => array(),
            'post' => array(),
            'files' => array(),
            'cookies' => array(),
            'url' => '',
            'base' => '',
            'webroot' => '',
            'input' => null,
        );
        $this->server = new ServerBag($server);
        $this->headers = new HeaderBag($this->server->getHeaders());
        $this->_setConfig($config);
    }

    /**
     * Process the config/settings data into properties.
     *
     * @param array $config The config data to use.
     * @return void
     */
    protected function _setConfig($config)
    {
        if (!empty($config['url']) && $config['url'][0] == '/') {
            $config['url'] = substr($config['url'], 1);
        }

        $this->url = $config['url'];
        $this->base = $config['base'];
        $this->cookies = $config['cookies'];
        $this->here = $this->base . '/' . $this->url;
        $this->webroot = $config['webroot'];

        if (isset($config['input'])) {
            $this->_input = $config['input'];
        }
        $config['post'] = $this->_processPost($config['post']);
        $this->data = $this->_processFiles($config['post'], $config['files']);
        $this->query = $this->_processGet($config['query']);
        $this->params = $config['params'];
    }

    /**
     * Sets the env('REQUEST_METHOD') based on the simulated _method HTTP override
     * value.
     *
     * @param array $data Array of post data.
     * @return array
     */
    protected function _processPost($data)
    {
        if (
                in_array(env('REQUEST_METHOD'), array('PUT', 'DELETE', 'PATCH')) &&
                strpos(env('CONTENT_TYPE'), 'application/x-www-form-urlencoded') === 0
        ) {
            $data = $this->input();
            parse_str($data, $data);
        }
        if (env('HTTP_X_HTTP_METHOD_OVERRIDE')) {
            $data['_method'] = env('HTTP_X_HTTP_METHOD_OVERRIDE');
        }
        if (isset($data['_method'])) {
            if (!$this->server->IsEmpty()) {
                $this->server->set('REQUEST_METHOD', $data['_method']);
            } else {
                $_ENV['REQUEST_METHOD'] = $data['_method'];
            }
            unset($data['_method']);
        }
        return $data;
    }

    /**
     * Process the GET parameters and move things into the object.
     *
     * @return void
     */
    protected function _processGet($query)
    {
        unset($query['/' . str_replace('.', '_', urldecode($this->url))]);
        if (strpos($this->url, '?') !== false) {
            list(, $querystr) = explode('?', $this->url);
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
    protected static function _url($config)
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            return $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '://') === false) {
            $uri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $uri = substr($_SERVER['REQUEST_URI'], strlen(FULL_BASE_URL));
        } elseif (isset($_SERVER['PHP_SELF']) && isset($_SERVER['SCRIPT_NAME'])) {
            $uri = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']);
        } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif ($var = env('argv')) {
            $uri = $var[0];
        }

        $base = $config['base'];

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
     * Returns a base URL and sets the proper webroot
     *
     * @return array Base URL, webroot dir ending in /
     */
    protected static function _base()
    {
        $base = $dir = $webroot = null;
        $config = Config::read('App');
        extract($config);

        if ($base !== false && $base !== null) {
            return array($base, $base . '/');
        }

        if (!$baseUrl) {
            $base = dirname(env('PHP_SELF'));

            if ($webroot === 'public' && $webroot === basename($base)) {
                $base = dirname($base);
            }
            if ($dir === 'App' && $dir === basename($base)) {
                $base = dirname($base);
            }

            if ($base === DS || $base === '.') {
                $base = '';
            }
            return array($base, $base . '/');
        }

        $file = '/' . basename($baseUrl);
        $base = dirname($baseUrl);

        if ($base === DS || $base === '.') {
            $base = '';
        }
        $webrootDir = $base . '/';

        $docRoot = env('DOCUMENT_ROOT');
        $docRootContainsWebroot = strpos($docRoot, $dir . '/' . $webroot);

        if (!empty($base) || !$docRootContainsWebroot) {
            if (strpos($webrootDir, '/' . $dir . '/') === false) {
                $webrootDir .= $dir . '/';
            }
            if (strpos($webrootDir, '/' . $webroot . '/') === false) {
                $webrootDir .= $webroot . '/';
            }
        }
        return array($base . $file, $webrootDir);
    }

    /**
     * Process uploaded files and move things onto the post data.
     *
     * @param array $data Post data to merge files onto.
     * @param array $files Uploaded files to merge in.
     * @return array merged post + file data.
     */
    protected function _processFiles($post, $files)
    {
        if (isset($files) && is_array($files)) {
            foreach ($files as $key => $data) {
                $this->_processFileData($post, '', $data, $key);
            }
        }
        return $post;
    }

    /**
     * Recursively walks the FILES array restructuring the data
     * into something sane and useable.
     *
     * @param string $path The dot separated path to insert $data into.
     * @param array $data The data to traverse/insert.
     * @param string $field The terminal field name, which is the top level key in $_FILES.
     * @param array $post The post data having files inserted into
     * @return void
     */
    protected function _processFileData(&$post, $path, $data, $field)
    {
        foreach ($data as $key => $fields) {
            $newPath = $key;
            if (!empty($path)) {
                $newPath = $path . '.' . $key;
            }
            if (is_array($fields)) {
                $this->_processFileData($post, $newPath, $fields, $field);
            } else {
                $newPath .= '.' . $field;
                $post = Hash::insert($post, $newPath, $fields);
            }
        }
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
     * Missing method handler, handles wrapping older style isAjax() type methods
     *
     * @param string $name The method called
     * @param array $params Array of parameters for the method call
     * @return mixed
     * @throws RuntimeException when an invalid method is called.
     */
    public function __call($name, $params)
    {
        if (strpos($name, 'is') === 0) {
            $type = strtolower(substr($name, 2));
            return $this->is($type);
        }
        throw new RuntimeException(__('Method %s does not exist', $name));
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
                return env($detect['env']) == $detect['value'];
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
     * Add a new detector to the list of detectors that a request can use.
     * There are several different formats and types of detectors that can be set.
     *
     * ### Environment value comparison
     *
     * An environment value comparison, compares a value fetched from `env()` to a known value
     * the environment value is equality checked against the provided value.
     *
     * e.g `addDetector('post', array('env' => 'REQUEST_METHOD', 'value' => 'POST'))`
     *
     * ### Pattern value comparison
     *
     * Pattern value comparison allows you to compare a value fetched from `env()` to a regular expression.
     *
     * e.g `addDetector('iphone', array('env' => 'HTTP_USER_AGENT', 'pattern' => '/iPhone/i'));`
     *
     * ### Option based comparison
     *
     * Option based comparisons use a list of options to create a regular expression.  Subsequent calls
     * to add an already defined options detector will merge the options.
     *
     * e.g `addDetector('mobile', array('env' => 'HTTP_USER_AGENT', 'options' => array('Fennec')));`
     *
     * ### Callback detectors
     *
     * Callback detectors allow you to provide a 'callback' type to handle the check.  The callback will
     * receive the request object as its only parameter.
     *
     * e.g `addDetector('custom', array('callback' => array('SomeClass', 'somemethod')));`
     *
     * ### Request parameter detectors
     *
     * Allows for custom detectors on the request parameters.
     *
     * e.g `addDetector('post', array('param' => 'requested', 'value' => 1)`
     *
     * @param string $name The name of the detector.
     * @param array $options  The options for the detector definition.  See above.
     * @return void
     */
    public function addDetector($name, $options)
    {
        $name = strtolower($name);
        if (isset($this->_detectors[$name]) && isset($options['options'])) {
            $options = Hash::merge($this->_detectors[$name], $options);
        }
        $this->_detectors[$name] = $options;
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
     * Add paths to the requests' paths vars.  This will overwrite any existing paths.
     * Provides an easy way to modify, here, webroot and base.
     *
     * @param array $paths Array of paths to merge in
     * @return Request the current object, you can chain this method.
     */
    public function addPaths($paths)
    {
        foreach (array('public', 'here', 'base') as $element) {
            if (isset($paths[$element])) {
                $this->{$element} = $paths[$element];
            }
        }
        return $this;
    }

    /**
     * Get the value of the current requests url.  Will include named parameters and querystring arguments.
     *
     * @param boolean $base Include the base path, set to false to trim the base path off.
     * @return string the current request url including query string args.
     */
    public function here($base = true)
    {
        $url = $this->here;
        if (!empty($this->query)) {
            $url .= '?' . http_build_query($this->query, null, '&');
        }
        if (!$base) {
            $url = preg_replace('/^' . preg_quote($this->base, '/') . '/', '', $url, 1);
        }
        return $url;
    }

    /**
     * Read an HTTP header from the Request information.
     *
     * @param string $name Name of the header you want.
     * @return mixed Either false on no header being set or the value of the header.
     */
    public static function header($name)
    {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (!empty($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        return false;
    }

    /**
     * Get the HTTP method used for this request.
     * There are a few ways to specify a method.
     *
     * - If your client supports it you can use native HTTP methods.
     * - You can set the HTTP-X-Method-Override header.
     * - You can submit an input with the name `_method`
     *
     * Any of these 3 approaches can be used to set the HTTP method used
     * by EasyFw internally, and will effect the result of this method.
     *
     * @return string The name of the HTTP method used.
     */
    public function method()
    {
        return env('REQUEST_METHOD');
    }

    /**
     * Get the host that the request was handled on.
     *
     * @return string
     */
    public function host()
    {
        return env('HTTP_HOST');
    }

    /**
     * Get the port the request was handled on.
     *
     * @return string
     */
    public function port()
    {
        if ($this->trustProxy && env('HTTP_X_FORWARDED_PORT')) {
            return env('HTTP_X_FORWARDED_PORT');
        }
        return env('SERVER_PORT');
    }

    /**
     * Get the current url scheme used for the request.
     *
     * e.g. 'http', or 'https'
     *
     * @return string The scheme used for the request.
     */
    public function scheme()
    {
        if ($this->trustProxy && env('HTTP_X_FORWARDED_PROTO')) {
            return env('HTTP_X_FORWARDED_PROTO');
        }
        return env('HTTPS') ? 'https' : 'http';
    }

    /**
     * Get the domain name and include $tldLength segments of the tld.
     *
     * @param integer $tldLength Number of segments your tld contains. For example: `example.com` contains 1 tld.
     *   While `example.co.uk` contains 2.
     * @return string Domain name without subdomains.
     */
    public function domain($tldLength = 1)
    {
        $segments = explode('.', $this->host());
        $domain = array_slice($segments, -1 * ($tldLength + 1));
        return implode('.', $domain);
    }

    /**
     * Get the subdomains for a host.
     *
     * @param integer $tldLength Number of segments your tld contains. For example: `example.com` contains 1 tld.
     *   While `example.co.uk` contains 2.
     * @return array of subdomains.
     */
    public function subdomains($tldLength = 1)
    {
        $segments = explode('.', $this->host());
        return array_slice($segments, 0, -1 * ($tldLength + 1));
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
        $raw = $this->parseAccept();
        $accept = array();
        foreach ($raw as $types) {
            $accept = array_merge($accept, $types);
        }
        if ($type === null) {
            return $accept;
        }
        return in_array($type, $accept);
    }

    /**
     * Parse the HTTP_ACCEPT header and return a sorted array with content types
     * as the keys, and pref values as the values.
     *
     * Generally you want to use Request::accept() to get a simple list
     * of the accepted content types.
     *
     * @return array An array of prefValue => array(content/types)
     */
    public function parseAccept()
    {
        return $this->_parseAcceptWithQualifier($this->header('accept'));
    }

    /**
     * Get the languages accepted by the client, or check if a specific language is accepted.
     *
     * Get the list of accepted languages:
     *
     * {{{ Request::acceptLanguage(); }}}
     *
     * Check if a specific language is accepted:
     *
     * {{{ Request::acceptLanguage('es-es'); }}}
     *
     * @param string $language The language to test.
     * @return If a $language is provided, a boolean. Otherwise the array of accepted languages.
     */
    public static function acceptLanguage($language = null)
    {
        $raw = static::_parseAcceptWithQualifier(static::header('Accept-Language'));
        $accept = array();
        foreach ($raw as $qualifier => $languages) {
            foreach ($languages as &$lang) {
                if (strpos($lang, '_')) {
                    $lang = str_replace('_', '-', $lang);
                }
                $lang = strtolower($lang);
            }
            $accept = array_merge($accept, $languages);
        }
        if ($language === null) {
            return $accept;
        }
        return in_array(strtolower($language), $accept);
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

        $languages = static::_parseAcceptWithQualifier(static::header('Accept-Language'));
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
                \Locale::setDefault($locale);
            }
        } catch (\Exception $e) {
            
        }
    }

    /**
     * Parse Accept* headers with qualifier options
     *
     * @param string $header
     * @return array
     */
    protected static function _parseAcceptWithQualifier($header)
    {
        $accept = array();
        $header = explode(',', $header);
        foreach (array_filter($header) as $value) {
            $prefPos = strpos($value, ';');
            if ($prefPos !== false) {
                $prefValue = substr($value, strpos($value, '=') + 1);
                $value = trim(substr($value, 0, $prefPos));
            } else {
                $prefValue = '1.0';
                $value = trim($value);
            }
            if (!isset($accept[$prefValue])) {
                $accept[$prefValue] = array();
            }
            if ($prefValue) {
                $accept[$prefValue][] = $value;
            }
        }
        krsort($accept);
        return $accept;
    }

    /**
     * Provides a read accessor for `$this->query`.  Allows you
     * to use a syntax similar to `Session` for reading url query data.
     *
     * @return mixed The value being read
     */
    public function query($name)
    {
        return Hash::get($this->query, $name);
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

    /**
     * Read data from `php://input`. Useful when interacting with XML or JSON
     * request body content.
     *
     * Getting input with a decoding function:
     *
     * `$this->request->input('json_decode');`
     *
     * Getting input using a decoding function, and additional params:
     *
     * `$this->request->input('Xml::build', array('return' => 'DOMDocument'));`
     *
     * Any additional parameters are applied to the callback in the order they are given.
     *
     * @param string $callback A decoding callback that will convert the string data to another
     *     representation. Leave empty to access the raw input data. You can also
     *     supply additional parameters for the decoding callback using var args, see above.
     * @return The decoded/processed request data.
     */
    public function input($callback = null)
    {
        $input = $this->_readInput();
        $args = func_get_args();
        if (!empty($args)) {
            $callback = array_shift($args);
            array_unshift($args, $input);
            return call_user_func_array($callback, $args);
        }
        return $input;
    }

    /**
     * Read cookie data from the request's cookie data.
     *
     * @param string $key The key you want to read.
     * @return null|string Either the cookie value, or null if the value doesn't exist.
     */
    public function cookie($key)
    {
        if (isset($this->cookies[$key])) {
            return $this->cookies[$key];
        }
        return null;
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
     * Read data from php://input, mocked in tests.
     *
     * @return string contents of php://input
     */
    protected function _readInput()
    {
        if (empty($this->_input)) {
            $fh = fopen('php://input', 'r');
            $content = stream_get_contents($fh);
            fclose($fh);
            $this->_input = $content;
        }
        return $this->_input;
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
     * Returns current script name.
     *
     * @return string
     */
    public function getScriptName()
    {
        return $this->server->getItem('SCRIPT_NAME', $this->server->getItem('ORIG_SCRIPT_NAME', ''));
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
     * @throws \UnexpectedValueException when the host name is invalid
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
            throw new \UnexpectedValueException('Invalid Host');
        }

        return $host;
    }

    protected static function prepareRequestUri()
    {
        $requestUri = '';

        if ($this->headers->contains('X_ORIGINAL_URL') && false !== stripos(PHP_OS, 'WIN')) {
            // IIS with Microsoft Rewrite Module
            $requestUri = $this->headers->getItem('X_ORIGINAL_URL');
        } elseif ($this->headers->contains('X_REWRITE_URL') && false !== stripos(PHP_OS, 'WIN')) {
            // IIS with ISAPI_Rewrite
            $requestUri = $this->headers->getItem('X_REWRITE_URL');
        } elseif ($this->server->getItem('IIS_WasUrlRewritten') == '1' && $this->server->getItem('UNENCODED_URL') != '') {
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

        if (basename($this->server->getItem('SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->getItem('SCRIPT_NAME');
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