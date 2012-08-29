<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @package       app
 * @since         EasyFramework v 0.2
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\View;

use Easy\Core\App;
use Easy\Core\Config;
use Easy\Controller\Controller;
use Easy\Routing\Mapper;
use Easy\Utility\Inflector;
use Easy\View\HelperCollection;

/**
 * View, the V in the MVC triad. View interacts with Helpers and view variables passed
 * in from the controller to render the results of the controller action.  Often this is HTML,
 * but can also take the form of JSON, XML, PDF's or streaming files.
 *
 * EasyFw uses a two-step-view pattern.  This means that the view content is rendered first,
 * and then inserted into the selected layout.  This also means you can pass data from the view to the
 * layout using `$this->set()`
 * 
 * @package       Easy.View
 * @property      FormHelper $Form
 * @property      HtmlHelper $Html
 * @property      NumberHelper $Number
 * @property      PaginatorHelper $Paginator
 * @property      SessionHelper $Session
 * @property      TimeHelper $Time
 */
class View
{

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

    function __construct(Controller $controller)
    {
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

    /**
     * Provides backwards compatibility access to the request object properties.
     * Also provides the params alias.
     *
     * @param $name string
     * @return void
     */
    public function __get($name)
    {
        if (isset($this->Helpers->{$name})) {
            $this->{$name} = $this->Helpers->{$name};
            return $this->Helpers->{$name};
        }
    }

    public function loadHelpers($controller)
    {
        $this->Helpers->init($controller);
    }

    /**
     * Gets the current active TemplateEngine
     *
     * @return object
     */
    public function getEngine()
    {
        return $this->engine;
    }

    public function getUrls($url = null)
    {
        if (is_null($url)) {
            return $this->urls;
        } else {
            return $this->urls [$url];
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getController()
    {
        return $this->controller;
    }

    /**
     * Sets the _escape() callback.
     *
     * @param $spec mixed The callback for _escape() to use.
     * @return View
     */
    public function setEscape($spec)
    {
        $this->_escape = $spec;
        return $this;
    }

    /**
     * Set encoding to use with htmlentities() and htmlspecialchars()
     *
     * @param $encoding string
     * @return View
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Return current escape encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    protected function loadEngine($engine = null)
    {
        if (is_null($engine)) {
            $engine = 'Smarty';
        }
        $engine = Inflector::camelize($engine);
        $viewEngineClass = App::classname($engine, 'View/Engine', 'Engine');

        if (class_exists($viewEngineClass)) {
            return new $viewEngineClass();
        }
        return false;
    }

    /**
     * Display a view
     * @param $view string The view's name to be show
     * @param $layout string The layout name to be rendered
     * @return View
     */
    function display($view, $layout, $ext = null, $output = true)
    {
        return $this->engine->display($layout, $view, $ext, $output);
    }

    /**
     * Defines a varible which will be passed to the view
     *
     * @param $var string The varible's name
     * @param $value mixed The varible's value
     */
    function set($var, $value)
    {
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
    public function escape($var)
    {
        if (in_array($this->_escape, array('htmlspecialchars', 'htmlentities'))) {
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
    private function buildUrls()
    {
        $newURls = array();
        if (!empty($this->urls)) {
            $base = Mapper::url();
            $urls = $this->createUrlsRecursive($this->urls, $base);
            $newURls = array_merge_recursive($urls, array(
                "base" => $base,
                "atual" => $base . Mapper::url(Mapper::here(), true)
                    ));
        }
        $this->set('url', $newURls);
    }

    private function createUrlsRecursive(Array $urls, $base)
    {
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
    private function buildLayouts()
    {
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
    private function buildElements()
    {
        if (isset($this->config ["elements"]) && is_array($this->config ["elements"])) {
            $elements = $this->config ["elements"];
            foreach ($elements as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

}