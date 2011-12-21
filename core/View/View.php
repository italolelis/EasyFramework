<?php

App::uses('Helper', 'Core/View');
App::uses('I18N', 'Core/Localization');

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
    protected $template;

    /**
     * Defines if the view will be rendered automatically
     */
    protected $autoRender = true;

    /**
     * Helpers to be used with the view
     * @var array
     */
    public $helpers = array('html', 'form');

    /**
     * Loaded Helper classes
     * @var array 
     */
    protected $loadedHelpers = array();

    function __construct() {
        //Instanciate a Engine
        $this->template = $this->getEngine();
        //Build the views urls
        $this->buildUrls();
        //Build the template language
        $this->buildLanguage();

        array_map(array($this, 'loadHelper'), $this->helpers);
    }

    protected function getEngine() {
        $view = Config::read('View');
        $engine = (isset($view['engine']) ? $view['engine'] : 'Smarty') . 'Engine';
        return ClassRegistry::load($engine, 'Core/View/Engine');
    }

    /**
     * Loads the helpers that was declared within the helpers array
     * @param string $helper Helper's name to be loaded
     * @return mixed The Helper object
     */
    public function loadHelper($helper) {
        $helper_class = Inflector::camelize($helper) . 'Helper';
        Helper::load($helper_class);

        $this->loadedHelpers[$helper] = new $helper_class($this);
        $this->set($helper, $this->loadedHelpers[$helper]);
        return $this->loadedHelpers[$helper];
    }

    public function getTemplate() {
        return $this->template;
    }

    public function getConfig() {
        return $this->template->getConfig();
    }

    public function getAutoRender() {
        return $this->autoRender;
    }

    public function setAutoRender($autoRender) {
        $this->autoRender = $autoRender;
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
                return $this->template->display($view, $ext);
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
        $this->template->set($var, $value);
    }

    /**
     * Build the urls used in the view
     * @since 0.1.2
     */
    private function buildUrls() {
        $this->buildStaticDomain();
        $config = $this->getConfig();

        if (isset($config['urls'])) {
            $base = Mapper::base() === "/" ? Mapper::domain() : Mapper::base();
            //Foreach url we verify if not contains an abslute url.
            //If not contains an abslute url we put the base domain before the url.
            foreach ($config["urls"] as $key => $value) {
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
        $this->set('url', isset($config['urls']) ? array_merge($config['urls'], $newURls) : "");
    }

    /**
     * Build the static domain var in the view
     * Statics domains are used to load static files like Imgs, css and js.
     * @todo Implement the static domain in the view
     */
    private function buildStaticDomain() {
        if (!Config::read("debug") && !is_null(Config::read('Assets.static_url'))) {
            Mapper::setDomain(Config::read('Assets.static_url'));
        }
    }

    /**
     * Build the template language based on the template's config
     * @todo Implement some way to pass the language param at the URL through GET request.
     */
    private function buildLanguage() {
        $config = $this->getConfig();

        if (isset($config["language"])) {
            $localization = I18N::instance();
            $localization->setLocale($config["language"]);
            $this->set("localization", $localization);
        }
    }

}

interface ITemplateEngine {

    public function display($view, $ext = "tpl");

    public function set($var, $value);

    public function getConfig();
}

?>
