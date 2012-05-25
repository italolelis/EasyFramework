<?php

App::uses('Inflector', 'Utility');

/**
 * Class: Mapper
 */
class Mapper {

    protected $prefixes = array();
    protected $routes = array();
    protected $base;
    protected $here;
    protected $domain;
    protected $root = null;

    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var ClassRegistry
     */
    protected static $instance;

    /**
     * Singleton instance
     *
     * @return ClassRegistry
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self ();
        }

        return self::$instance;
    }

    public static function here() {
        $self = self::getInstance();
        if (is_null($self->here)) {
            if (array_key_exists('REQUEST_URI', $_SERVER)) {
                $start = strlen(self::base());
                $request_uri = substr(env('REQUEST_URI'), $start);
                $self->here = self::normalize($request_uri);
            } else {
                $self->here = '/';
            }
        }

        return $self->here;
    }

    public static function base() {
        $self = self::getInstance();

        if (is_null($self->base)) {
            $self->base = dirname(env('PHP_SELF'));

            while (in_array(basename($self->base), array("app", "webroot"))) {
                $self->base = dirname($self->base);
            }
            if ($self->base == DS || $self->base == ".") {
                $self->base = "/";
            }

            if ($self->base === DS || $self->base === '.') {
                $self->base = '/';
            }
        }

        return $self->base;
    }

    public static function domain() {
        $self = self::getInstance();

        if (is_null($self->domain)) {
            if (array_key_exists('REQUEST_URI', $_SERVER)) {
                $s = array_key_exists('HTTPS', $_SERVER) ? 's' : '';
                $self->domain = 'http' . $s . '://' . env('HTTP_HOST');
            } else {
                $self->domain = 'http://localhost';
            }
        }

        return $self->domain;
    }

    public static function setDomain($domain) {
        $self = self::getInstance();
        $self->domain = $domain;
    }

    public static function normalize($url) {
        if (!self::isExternal($url)) {
            $url = preg_replace('%/+%', '/', $url);
            $url = '/' . trim($url, '/');
        }

        return $url;
    }

    public static function setRoot($controller) {
        self::getInstance()->root = $controller;
    }

    public static function getRoot() {
        $self = self::getInstance();
        return is_null($self->root) ? 'Home' : $self->root;
    }

    /**
     * Finds URL for specified action.
     *
     * Returns an URL pointing to a combination of controller and action. Param
     * $url can be:
     *
     * - Empty - the method will find address to actual controller/action.
     * - '/' - the method will find base URL of application.
     * - A combination of controller/action - the method will find url for it.
     *
     * There are a few 'special' parameters that can change the final URL string that is generated
     *
     * - `base` - Set to false to remove the base path from the generated url. If your application
     *   is not in the root directory, this can be used to generate urls that are 'cake relative'.
     *   cake relative urls are required when using requestAction.
     * - `?` - Takes an array of query string parameters
     * - `#` - Allows you to set url hash fragments.
     * - `full_base` - If true the `FULL_BASE_URL` constant will be prepended to generated urls.
     *
     * @param mixed $url Cake-relative URL, like "/products/edit/92" or "/presidents/elect/4"
     *   or an array specifying any of the following: 'controller', 'action',
     *   and/or 'plugin', in addition to named arguments (keyed array elements),
     *   and standard URL arguments (indexed array elements)
     * @param mixed $full If (bool) true, the full base URL will be prepended to the result.
     *   If an array accepts the following keys
     *    - escape - used when making urls embedded in html escapes query string '&'
     *    - full - if true the full base URL will be prepended.
     * @return string Full translated URL with base path.
     */
    public static function url($url, $full = false, $base = '/') {
        if (is_array($url)) {
            $url = self::reverse($url);
        } else if (self::isExternal($url)) {
            return $url;
        }

        if (!self::isRoot($url)) {
            if (!self::isHash($url)) {
                $url = $base . $url;
            }
            if ($base == '/') {
                $url = self::here() . $url;
            }
        }

        $url = self::normalize(self::base() . $url);

        return $full ? self::domain() . $url : $url;
    }

    /**
     * Generates a well-formed querystring from $q
     *
     * @param string|array $q Query string Either a string of already compiled query string arguments or
     *    an array of arguments to convert into a query string.
     * @param array $extra Extra querystring parameters.
     * @param boolean $escape Whether or not to use escaped &
     * @return array
     */
    public static function queryString($q, $extra = array(), $escape = false) {
        if (empty($q) && empty($extra)) {
            return null;
        }
        $join = '&';
        if ($escape === true) {
            $join = '&amp;';
        }
        $out = '';

        if (is_array($q)) {
            $q = array_merge($extra, $q);
        } else {
            $out = $q;
            $q = $extra;
        }
        $out .= http_build_query($q, null, $join);
        if (isset($out[0]) && $out[0] != '?') {
            $out = '?' . $out;
        }
        return $out;
    }

    public static function isExternal($path) {
        return preg_match('/^[\w]+:/', $path);
    }

    public static function isRoot($url) {
        return substr($url, 0, 1) == '/';
    }

    public static function isHash($url) {
        return substr($url, 0, 1) == '#';
    }

    public static function reverse($path) {
        $here = self::parse();
        $params = $here ['named'];
        $path = array_merge(array('prefix' => $here ['prefix'], 'controller' => $here ['controller'], 'action' => $here ['action'], 'params' => $here ['params']), $params, $path);
        $nonParams = array('prefix', 'controller', 'action', 'params');
        $url = '';
        foreach ($path as $key => $value) {
            if (!in_array($key, $nonParams)) {
                $url .= '/' . $key . ':' . $value;
            } else if (!is_null($value)) {
                if ($key == 'action' && $filtered = self::filterAction($value)) {
                    $value = $filtered ['action'];
                } else if ($key == 'params') {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                    $value = join('/', $value);
                }
                $url .= '/' . $value;
            }
        }

        return $url;
    }

    public static function prefix($prefix) {
        self::getInstance()->prefixes [] = $prefix;
    }

    public static function unsetPrefix($prefix) {
        unset(self::getInstance()->prefixes [$prefix]);
    }

    public static function prefixes() {
        return self::getInstance()->prefixes;
    }

    public static function connect($url, $route) {
        if (is_array($route)) {
            $route = self::reverse($route);
        }
        $url = self::normalize($url);
        self::getInstance()->routes [$url] = rtrim($route, '/');
    }

    public static function disconnect($url) {
        $url = rtrim($url, '/');
        unset(self::getInstance()->routes [$url]);
    }

    public static function match($check, $url = null) {
        if (is_null($url)) {
            $url = self::here();
        }
        $check = '%^' . str_replace(array(':any', ':fragment', ':num'), array('(.+)', '([^\/]+)', '([0-9]+)'), $check) . '/?$%';
        return preg_match($check, $url);
    }

    public static function getRoute($url) {
        $self = self::getInstance();
        foreach ($self->routes as $map => $route) {
            if (self::match($map, $url)) {
                $map = '%^' . str_replace(array(':any', ':fragment', ':num'), array('(.+)', '([^\/]+)', '([0-9]+)'), $map) . '/?$%';
                $url = preg_replace($map, $route, $url);
                break;
            }
        }
        return self::normalize($url);
    }

    /**
     * Parse a URL
     *
     * @param $url string
     *       	 the URL to be parsed. If none URL was given than the default
     *       	 will be used.
     * @return array
     */
    public static function parse($url = null) {
        // If there's no URL, than get the atual URL.
        $here = self::normalize(is_null($url) ? self::here() : $url );
        $url = self::getRoute($here);
        $prefixes = join('|', self::prefixes());

        $path = array();
        $parts = array('here', 'prefix', 'controller', 'action', 'extension', 'params', 'queryString');
        preg_match('/^\/(?:(' . $prefixes . ')(?:\/|(?!\w)))?(?:([a-z_-]*)\/?)?(?:([a-z_-]*)\/?)?(?:\.([\w]+))?(?:\/?([^?]+))?(?:\?(.*))?/i', $url, $reg);

        foreach ($parts as $k => $key) {
            $path [$key] = isset($reg [$k]) ? $reg [$k] : null;
        }

        $path ['named'] = $path ['params'] = array();
        if (isset($reg [5])) {
            foreach (explode('/', $reg [5]) as $param) {
                if (preg_match('/([^:]*):([^:]*)/', $param, $reg)) {
                    $path ['named'] [$reg [1]] = urldecode($reg [2]);
                } else if ($param != '') {
                    $path ['params'] [] = urldecode($param);
                }
            }
        }

        $path ['here'] = $here;
        if (empty($path ['controller'])) {
            $path ['controller'] = Inflector::camelize(self::getRoot());
        }
        if (empty($path ['action'])) {
            $path ['action'] = 'index';
        }
        $filtered = self::filterAction($path ['action']);
        if ($filtered) {
            $path ['prefix'] = $filtered ['prefix'];
            $path ['action'] = Inflector::hyphenToUnderscore($filtered ['action']);
        }
        if (!empty($path ['prefix'])) {
            $path ['action'] = $path ['prefix'] . '_' . $path ['action'];
        }
        if (empty($path ['id'])) {
            $path ['id'] = null;
        }
        if (empty($path ['extension'])) {
            $path ['extension'] = 'htm';
        }
        if (!empty($path ['queryString'])) {
            parse_str($path ['queryString'], $queryString);
            $path ['named'] = array_merge($path ['named'], $queryString);
        }

        return $path;
    }

    public static function filterAction($action) {
        if (strpos($action, '_') !== false) {
            foreach (self::prefixes() as $prefix) {
                if (strpos($action, $prefix) === 0) {
                    return array('action' => substr($action, strlen($prefix) + 1), 'prefix' => $prefix);
                }
            }
        }
        return false;
    }

    /**
     * Getter para Mapper::atual.
     *
     * @return string Valor da url atual
     */
    public static function atual() {
        return self::normalize(str_replace(basename(dirname(APP_PATH)), "", env('REQUEST_URI')));
    }

}