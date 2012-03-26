<?php

App::uses('Set', 'Core/Utility');

/**
 * A class that helps wrap Request information and particulars about a single request.
 * Provides methods commonly used to introspect on the request headers and request body.
 *
 * Has both an Array and Object interface. You can access framework parameters using indexes:
 *
 * `$request['controller']` or `$request->controller`.
 *
 * @package       Network
 */
class Request implements ArrayAccess {

    /**
     * Array of parameters parsed from the url.
     * @var array
     */
    protected $pass = array(
        'params' => null,
        'controller' => null,
        'action' => null,
    );
    public $data;

    /**
     * The url string used for the request.
     * @var string
     */
    public $url;

    /**
     * Array of querystring arguments
     * @var array
     */
    public $query = array();

    /**
     * The built in detectors used with `is()` can be modified with `addDetector()`.
     *
     * There are several ways to specify a detector, see Request::addDetector() for the
     * various formats and ways to define detectors.
     *
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
                'Android', 'AvantGo', 'BlackBerry', 'DoCoMo', 'Fennec', 'iPod', 'iPhone',
                'J2ME', 'MIDP', 'NetFront', 'Nokia', 'Opera Mini', 'Opera Mobi', 'PalmOS', 'PalmSource',
                'portalmmm', 'Plucker', 'ReqwirelessWeb', 'SonyEricsson', 'Symbian', 'UP\\.Browser',
                'webOS', 'Windows CE', 'Xiino'
        ))
    );

    function __construct($url, $parseEnvironment = true) {
        $this->url = $url;

        if ($parseEnvironment) {
            $this->_processPost();
            $this->_processGet();
            $this->_processFiles();
        }
    }

    /**
     * Magic get method allows access to parsed routing parameters directly on the object.
     *
     * Allows access to `$this->params['controller']` via `$this->controller`
     *
     * @param string $name The property being accessed.
     * @return mixed Either the value of the parameter or null.
     */
    public function __get($name) {
        if (isset($this->pass[$name])) {
            return $this->pass[$name];
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
    public function __isset($name) {
        return isset($this->pass[$name]);
    }

    /**
     * process the post data and set what is there into the object.
     * processed data is available at $this->data
     *
     * @return void
     */
    protected function _processPost() {
        $this->data = $_POST;
        if (ini_get('magic_quotes_gpc') === '1') {
            $this->data = stripslashes_deep($this->data);
        }
        if (env('HTTP_X_HTTP_METHOD_OVERRIDE')) {
            $this->data['_method'] = env('HTTP_X_HTTP_METHOD_OVERRIDE');
        }
        if (isset($this->data['_method'])) {
            if (!empty($_SERVER)) {
                $_SERVER['REQUEST_METHOD'] = $this->data['_method'];
            } else {
                $_ENV['REQUEST_METHOD'] = $this->data['_method'];
            }
            unset($this->data['_method']);
        }
        if (isset($this->data['data'])) {
            $data = $this->data['data'];
            unset($this->data['data']);
            $this->data = Set::merge($this->data, $data);
        }
    }

    /**
     * Process the GET parameters and move things into the object.
     *
     * @return void
     */
    protected function _processGet() {
        if (ini_get('magic_quotes_gpc') === '1') {
            $query = stripslashes_deep($_GET);
        } else {
            $query = $_GET;
        }

        unset($query['/' . str_replace('.', '_', $this->url)]);
        if (strpos($this->url, '?') !== false) {
            list(, $querystr) = explode('?', $this->url);
            parse_str($querystr, $queryArgs);
            $query += $queryArgs;
        }
        if (isset($this->pass['url'])) {
            $query = array_merge($this->pass['url'], $query);
        }
        $this->query = $query;
    }

    /**
     * Process $_FILES and move things into the object.
     *
     * @return void
     */
    protected function _processFiles() {
        if (isset($_FILES) && is_array($_FILES)) {
            foreach ($_FILES as $name => $data) {
                if ($name != 'data') {
                    $this->pass['form'][$name] = $data;
                }
            }
        }

        if (isset($_FILES['data'])) {
            foreach ($_FILES['data'] as $key => $data) {
                foreach ($data as $model => $fields) {
                    if (is_array($fields)) {
                        foreach ($fields as $field => $value) {
                            if (is_array($value)) {
                                foreach ($value as $k => $v) {
                                    $this->data[$model][$field][$k][$key] = $v;
                                }
                            } else {
                                $this->data[$model][$field][$key] = $value;
                            }
                        }
                    } else {
                        $this->data[$model][$key] = $fields;
                    }
                }
            }
        }
    }

    /**
     * Get the languages accepted by the client, or check if a specific language is accepted.
     *
     * Get the list of accepted languages:
     *
     * {{{ CakeRequest::acceptLanguage(); }}}
     *
     * Check if a specific language is accepted:
     *
     * {{{ CakeRequest::acceptLanguage('es-es'); }}}
     *
     * @param string $language The language to test.
     * @return If a $language is provided, a boolean. Otherwise the array of accepted languages.
     */
    public static function acceptLanguage($language = null) {
        $accepts = preg_split('/[;,]/', self::header('Accept-Language'));
        foreach ($accepts as &$accept) {
            $accept = strtolower($accept);
            if (strpos($accept, '_') !== false) {
                $accept = str_replace('_', '-', $accept);
            }
        }
        if ($language === null) {
            return $accepts;
        }
        return in_array($language, $accepts);
    }

    /**
     * Get the IP the client is using, or says they are using.
     *
     * @param boolean $safe Use safe = false when you think the user might manipulate their HTTP_CLIENT_IP
     *   header.  Setting $safe = false will will also look at HTTP_X_FORWARDED_FOR
     * @return string The client IP.
     */
    public function clientIp($safe = true) {
        if (!$safe && env('HTTP_X_FORWARDED_FOR') != null) {
            $ipaddr = preg_replace('/(?:,.*)/', '', env('HTTP_X_FORWARDED_FOR'));
        } else {
            if (env('HTTP_CLIENT_IP') != null) {
                $ipaddr = env('HTTP_CLIENT_IP');
            } else {
                $ipaddr = env('REMOTE_ADDR');
            }
        }

        if (env('HTTP_CLIENTADDRESS') != null) {
            $tmpipaddr = env('HTTP_CLIENTADDRESS');

            if (!empty($tmpipaddr)) {
                $ipaddr = preg_replace('/(?:,.*)/', '', $tmpipaddr);
            }
        }
        return trim($ipaddr);
    }

    /**
     * Check whether or not a Request is a certain type.  Uses the built in detection rules
     * as well as additional rules defined with Request::addDetector().  Any detector can be called
     * as `is($type)` or `is$Type()`.
     *
     * @param string $type The type of request you want to check.
     * @return boolean Whether or not the request is the type you are checking.
     */
    public function is($type) {
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
        if (isset($detect['callback']) && is_callable($detect['callback'])) {
            return call_user_func($detect['callback'], $this);
        }
        return false;
    }

    /**
     * Read an HTTP header from the Request information.
     *
     * @param string $name Name of the header you want.
     * @return mixed Either false on no header being set or the value of the header.
     */
    public static function header($name) {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (!empty($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        return false;
    }

    /**
     * Provides a read/write accessor for `$this->data`.  Allows you
     * to use a syntax similar to `CakeSession` for reading post data.
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
    public function data($name) {
        $args = func_get_args();
        if (count($args) == 2) {
            $this->data = Set::insert($this->data, $name, $args[1]);
            return $this;
        }
        return Set::classicExtract($this->data, $name);
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
     * by EasyFramework internally, and will effect the result of this method.
     *
     * @return string The name of the HTTP method used.
     */
    public function method() {
        return env('REQUEST_METHOD');
    }

    /**
     * Get the host that the request was handled on.
     *
     * @return void
     */
    public function host() {
        return env('HTTP_HOST');
    }

    /**
     * Returns the referer that referred this request.
     *
     * @param boolean $local Attempt to return a local address. Local addresses do not contain hostnames.
     * @return string The referring address for this request.
     */
    public function referer($local = false) {
        $ref = env('HTTP_REFERER');
        $forwarded = env('HTTP_X_FORWARDED_HOST');
        if ($forwarded) {
            $ref = $forwarded;
        }

        $base = Mapper::base();

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
     * Get the domain name and include $tldLength segments of the tld.
     *
     * @param integer $tldLength Number of segments your tld contains. For example: `example.com` contains 1 tld.
     *   While `example.co.uk` contains 2.
     * @return string Domain name without subdomains.
     */
    public function domain($tldLength = 1) {
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
    public function subdomains($tldLength = 1) {
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
     * `$this->request->accepts('json');`
     *
     * This method will order the returned content types by the preference values indicated
     * by the client.
     *
     * @param string $type The content type to check for.  Leave null to get all types a client accepts.
     * @return mixed Either an array of all the types the client accepts or a boolean if they accept the
     *   provided type.
     */
    public function accepts($type = null) {
        $raw = $this->parseAccept();
        $accept = array();
        foreach ($raw as $value => $types) {
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
     * Generally you want to use CakeRequest::accept() to get a simple list
     * of the accepted content types.
     *
     * @return array An array of prefValue => array(content/types)
     */
    public function parseAccept() {
        $accept = array();
        $header = explode(',', $this->header('accept'));
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
     * recieve the request object as its only parameter.
     *
     * e.g `addDetector('custom', array('callback' => array('SomeClass', 'somemethod')));`
     *
     * @param string $name The name of the detector.
     * @param array $options  The options for the detector definition.  See above.
     * @return void
     */
    public function addDetector($name, $options) {
        if (isset($this->_detectors[$name]) && isset($options['options'])) {
            $options = Set::merge($this->_detectors[$name], $options);
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
    public function addParams($params) {
        $this->pass = array_merge($this->pass, (array) $params);
        return $this;
    }

    /**
     * Array access read implementation
     *
     * @param string $name Name of the key being accessed.
     * @return mixed
     */
    public function offsetGet($name) {
        if (isset($this->pass[$name])) {
            return $this->pass[$name];
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
    public function offsetSet($name, $value) {
        $this->pass[$name] = $value;
    }

    /**
     * Array access isset() implementation
     *
     * @param string $name thing to check.
     * @return boolean
     */
    public function offsetExists($name) {
        return isset($this->pass[$name]);
    }

    /**
     * Array access unset() implementation
     *
     * @param string $name Name to unset.
     * @return void
     */
    public function offsetUnset($name) {
        unset($this->pass[$name]);
    }

}

?>
