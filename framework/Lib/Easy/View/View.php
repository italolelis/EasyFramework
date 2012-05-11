<?php

App::uses('I18N', 'Localization');
App::uses('ITemplateEngine', "View");
App::uses('HelperCollection', "View");

/**
 * Class: View
 *
 * Views are the HTML, CSS and Javascript pages that will be shown to the users.
 *
 * Can be an view static and dynamic, a dynamic view uses the smarty tags to abstract
 * php's logic from the view.
 *
 * A view can contain diferents layouts, like headers, footers adn sidebars for each template
 * (view).
 *
 * A typical view will look something like this
 *
 * (start code)
 * <html>
 * <head></head>
 * <body>
 * <h1>{$articles}</h1>
 * </body>
 * </html>
 * (end)
 */
class View {

    /**
     * The controller which control the view
     * @var Controller 
     */
    protected $controller;

    /**
     * Helpers collection
     *
     * @var HelperCollection
     */
    protected $Helpers = array();

    /**
     * Callback for escaping.
     *
     * @var string
     */
    protected $_escape = 'htmlspecialchars';

    /**
     * Encoding to use in escaping mechanisms; defaults to utf-8
     *
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * ITemplateEngine object
     *
     * @var object
     */
    protected $engine;

    /**
     * View Config
     *
     * @var array
     */
    protected $config;

    /**
     * All Urls defined at the config array
     *
     * @var array
     */
    protected $urls = array();

    function __construct(Controller $controller) {
        $this->controller = $controller;

        $this->config = Config::read('View');
        // Instanciate a Engine
        $this->engine = $this->loadEngine(Config::read('View.engine'));
        $this->urls = Config::read('View.urls');
        // Build the views urls
        $this->buildUrls();
        // Build the template language
        $this->buildLayouts();
        // Build the template language
        $this->buildElements();

        $this->Helpers = new HelperCollection($this);
        
        // Loads all associate helpers
        $this->loadHelpers($controller);
    }

    public function loadHelpers($controller) {
        $this->Helpers->init($controller);
    }

    /**
     * Gets the current active TemplateEngine
     *
     * @return object
     */
    public function getEngine() {
        return $this->engine;
    }

    public function getUrls($url = null) {
        if (is_null($url)) {
            return $this->urls;
        } else {
            return $this->urls [$url];
        }
    }

    public function getConfig() {
        return $this->config;
    }

    public function getController() {
        return $this->controller;
    }

    /**
     * Sets the _escape() callback.
     *
     * @param $spec mixed The callback for _escape() to use.
     * @return View
     */
    public function setEscape($spec) {
        $this->_escape = $spec;
        return $this;
    }

    /**
     * Set encoding to use with htmlentities() and htmlspecialchars()
     *
     * @param $encoding string
     * @return View
     */
    public function setEncoding($encoding) {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Return current escape encoding
     *
     * @return string
     */
    public function getEncoding() {
        return $this->_encoding;
    }

    protected function loadEngine($engine = null) {
        if (is_null($engine)) {
            $engine = 'Smarty';
        }
        $engine = Inflector::camelize($engine . 'Engine');
        return ClassRegistry::load($engine, 'View/Engine');
    }

    /**
     * Display a view
     *
     * @param $view string The view's name to be show
     * @param $ext string The archive extension. The default is '.tpl'
     * @return View
     */
    function display($view, $ext = "tpl") {
        // If the view exists...
        if (App::path("View", $view, $ext)) {
            // ...display it
            return $this->engine->display($view, $ext);
        } else {
            // ...or throw an MissingViewException
            $errors = explode("/", $view);
            throw new MissingViewException(null, array(
                "view" => $errors [1] . ".tpl",
                "controller" => $errors [0],
                "action" => $errors [1],
                "title" => 'View Not Found'
            ));
        }
    }

    /**
     * Defines a varible which will be passed to the view
     *
     * @param $var string The varible's name
     * @param $value mixed The varible's value
     */
    function set($var, $value) {
        $this->engine->set($var, $value);
    }

    /**
     * Escapes a value for output in a view script.
     *
     * If escaping mechanism is one of htmlspecialchars or htmlentities, uses
     * {@link $_encoding} setting.
     *
     * @param $var mixed The output to escape.
     * @return mixed The escaped value.
     */
    public function escape($var) {
        if (in_array($this->_escape, array('htmlspecialchars',
                    'htmlentities'))) {
            return call_user_func($this->_escape, $var, ENT_COMPAT, $this->_encoding);
        }

        if (func_num_args() == 1) {
            return call_user_func($this->_escape, $var);
        }
        $args = func_get_args();
        return call_user_func_array($this->_escape, $args);
    }

    /**
     * Build the urls used in the view
     *
     * @since 0.1.2
     */
    private function buildUrls() {
        $newURls = array();
        if (!empty($this->urls)) {
            $base = Mapper::base() === "/" ? Mapper::domain() : Mapper::domain() . Mapper::base();
            $urls = $this->createUrlsRecursive($this->urls, $base);
            $newURls = array_merge_recursive($urls, array(
                "base" => $base,
                "atual" => $base . Mapper::atual()
                    ));
        }
        $this->set('url', $newURls);
    }

    private function createUrlsRecursive(Array $urls, $base) {
        $newURls = array();
        foreach ($urls as $key => $value) {
            if (is_array($value)) {
                $newURls [$key] = $this->createUrlsRecursive($value, $base);
            } else {
                if (!strstr($value, "http://") && !strstr($value, "https://")) {
                    $newURls [$key] = $base . "/" . $value;
                } else {
                    $newURls [$key] = $value;
                }
            }
        }
        return $newURls;
    }

    /**
     * Build the includes vars for the views.
     * This makes the call more friendly.
     *
     * @since 0.1.5
     */
    private function buildLayouts() {
        if (isset($this->config ["layouts"]) && is_array($this->config ["layouts"])) {
            $layouts = $this->config ["layouts"];
            foreach ($layouts as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Build the includes vars for the views.
     * This makes the call more friendly.
     *
     * @since 0.1.5
     */
    private function buildElements() {
        if (isset($this->config ["elements"]) && is_array($this->config ["elements"])) {
            $elements = $this->config ["elements"];
            foreach ($elements as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

}

?>
