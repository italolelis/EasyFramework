<?php

App::uses('I18N', 'Core/Localization');
App::uses('ITemplateEngine', "Core/View");

/**
  Class: View

  Views are the HTML, CSS and Javascript pages that will be shown to the users.

  Can be an view static and dynamic, a dynamic view uses the smarty tags to abstract
  php's logic from the view.

  A view can contain diferents layouts, like headers, footers adn sidebars for each template (view).

  A typical view will look something like this

  (start code)
  <html>
  <head></head>
  <body>
  <h1>{$articles}</h1>
  </body>
  </html>
  (end)
 */
class View {

    /**
     * ITemplateEngine object
     * @var object 
     */
    protected $engine;

    /**
     * View Config
     * @var array 
     */
    protected $config;

    /**
     * Defines if the view will be rendered automatically
     * @var bool
     */
    protected $autoRender = true;

    /**
     * All Urls defined at the config array
     * @var array 
     */
    protected $urls = array();

    function __construct() {
        $this->config = Config::read('View');
        //Instanciate a Engine
        $this->engine = $this->loadEngine(Config::read('View.engine'));
        $this->urls = Config::read('View.urls');
        //Build the views urls
        $this->buildUrls();
        //Build the template language
        $this->setLanguage(Config::read('View.language'));
        //Build the template language
        $this->buildLayouts();
        //Build the template language
        $this->buildElements();
    }

    /**
     * Gets the current active TemplateEngine
     * @return object 
     */
    public function getEngine() {
        return $this->engine;
    }

    public function getUrls($url = null) {
        if (is_null($url)) {
            return $this->urls;
        } else {
            return $this->urls[$url];
        }
    }

    public function getConfig() {
        return $this->config;
    }

    public function getAutoRender() {
        return $this->autoRender;
    }

    public function setAutoRender($autoRender) {
        $this->autoRender = $autoRender;
    }

    protected function loadEngine($engine = null) {
        if (is_null($engine)) {
            $engine = 'Smarty';
        }
        $engine = Inflector::camelize($engine . 'Engine');
        return ClassRegistry::load($engine, 'Core/View/Engine');
    }

    /**
     * Display a view
     * @param string $view The view's name to be show
     * @param string $ext The archive extension. The default is '.tpl'
     * @return View 
     */
    function display($view, $ext = "tpl") {
        if ($this->getAutoRender()) {
            // If the view exists...
            if (App::path("View", $view, $ext)) {
                //...display it
                return $this->engine->display($view, $ext);
            } else {
                //...or throw an MissingViewException
                $errors = explode("/", $view);
                throw new MissingViewException(array("view" => get_called_class($this), "controller" => $errors[0], "action" => $errors[1]));
            }
        }
    }

    /**
     * Defines a varible which will be passed to the view
     * @param string $var The varible's name
     * @param mixed $value The varible's value
     */
    function set($var, $value) {
        $this->engine->set($var, $value);
    }

    /**
     * Build the urls used in the view
     * @since 0.1.2
     */
    private function buildUrls() {
        if (!is_null($this->urls)) {
            $base = Mapper::base() === "/" ? Mapper::domain() : Mapper::base();
            //Foreach url we verify if not contains an abslute url.
            //If not contains an abslute url we put the base domain before the url.
            foreach ($this->urls as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (!strstr($v, "http://"))
                            $newURls[$key][$k] = $base . "/" . $v;
                    }
                } else {
                    if (!strstr($value, "http://"))
                        $newURls[$key] = $base . "/" . $value;
                }
            }
            $newURls = array_merge($newURls, array("base" => $base, "atual" => $base . Mapper::atual()));
        }
        $this->set('url', isset($this->urls) ? array_merge($this->urls, $newURls) : "");
    }

    /**
     * Build the template language based on the template's config
     * @todo Implement some way to pass the language param at the URL through GET request.
     */
    private function setLanguage($language = null) {
        if (!is_null($language)) {
            $localization = I18N::instance();
            $localization->setLocale($language);
            $this->set("localization", $localization);
        }
    }

    /**
     * Build the includes vars for the views. This makes the call more friendly.
     * @since 0.1.5
     */
    private function buildLayouts() {
        $layouts = $this->config["layouts"];
        if (isset($layouts) && is_array($layouts)) {
            foreach ($layouts as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Build the includes vars for the views. This makes the call more friendly.
     * @since 0.1.5
     */
    private function buildElements() {
        $elements = $this->config["elements"];
        if (isset($elements) && is_array($elements)) {
            foreach ($elements as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

}

?>
