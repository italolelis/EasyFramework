<?php

App::import("Core", "Localization/I18N");

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

  Dependencies:
  - <Smarty>
 */
class View {

    /**
     * Smarty Object
     * @var Smarty 
     */
    protected $template;

    /**
     * Template Config
     * @var type 
     */
    protected $config = array();

    /**
     * Defines if the view will be rendered automatically
     */
    protected $autoRender = true;

    /**
     * Layout used to display the views
     */
    protected $layout = null;

    function __construct() {
        //Loads the template config
        $this->config = Config::read('template');
        //Instanciate a Smarty object
        $this->template = new Smarty();
        //Build the template directory
        $this->buildTemplateDir();
        //Build the views urls
        $this->buildUrls();
        //Build the layouts vars
        $this->buildLayouts();
        //Build the cache
        $this->buildCache();
        //Build the template language
        $this->buildLanguage();
    }

    public function getConfig() {
        return $this->config;
    }

    public function getLayout() {
        return $this->layout;
    }

    public function setLayout($layout) {
        $this->layout = $layout;
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
            $layout = isset($this->layout) ? $this->layout . '/' : null;
            // If the view exists...
            if (App::path("View", $layout . $view, $ext)) {
                //...display it
                return $this->template->display("file:{$layout}{$view}.{$ext}");
            } else {
                //...or throw an MissingViewException
                $errors = explode("/", $view);
                throw new MissingViewException(array("controller" => $errors[0], "action" => $errors[1]));
            }
        }
    }

    /**
     * Defines a varible which will be passed to the view
     * @param string $var The varible's name
     * @param mixed $value The varible's value
     */
    function set($var, $value) {
        $this->template->assign($var, $value);
    }

    /**
     * Set the cache to a view
     * @param int $time The time in milliseconds that the cache stay active
     */
    function setCache($time = 3600) {
        $this->template->setCaching(Smarty::CACHING_LIFETIME_SAVED);
        $this->template->setCacheLifetime($time);
    }

    /**
     * Clean the cache of a view
     * @param int $template_name The view's name
     */
    function clearCache($template_name) {
        $this->template->clearCache($template_name);
    }

    /**
     * Clean the application's cache
     */
    function clearAllCache() {
        $this->template->clearAllCache();
    }

    /**
     * Defines the templates dir
     * @since 0.1.2
     */
    private function buildTemplateDir() {
        if (isset($this->config["templateDir"]) && is_array($this->config["templateDir"])) {
            $this->template->setTemplateDir($this->config["templateDir"]);
        } else {
            $this->template->setTemplateDir(array("views" => VIEW_PATH, 'layouts' => LAYOUT_PATH));
        }
    }

    /**
     * Build the urls used in the view
     * @since 0.1.2
     */
    private function buildUrls() {
        $this->buildStaticDomain();
        if (isset($this->config['urls'])) {
            $newURls = array();
            $base = Mapper::base() === "/" ? Mapper::domain() : Mapper::base();
            //Pegamos o mapeamento de url's
            foreach ($this->config["urls"] as $key => $value) {
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
        $this->set('url', isset($this->config['urls']) ? array_merge($this->config['urls'], $newURls) : "");
    }

    /**
     * Build the static domain var in the view
     * Statics domains are used to load static files like Imgs, css and js.
     * @todo Implement the static domain in the view
     */
    private function buildStaticDomain() {
        if (!Config::read("debug") && !is_null(Config::read('staticDomain'))) {
            Mapper::setDomain(Config::read('staticDomain'));
        }
    }

    /**
     * Constroi os includes caso estejam setados na configuração
     * @since 0.1.5
     */
    private function buildLayouts() {
        if (isset($this->config["layout"])) {
            if (is_array($this->config["layout"])) {
                foreach ($this->config["layout"] as $key => $value) {
                    $this->set($key, $value);
                }
            }
        }
    }

    /**
     * Constroi o cache padrão para as views, caso estejam setados na configuração
     * @since 0.1.6
     */
    private function buildCache() {
        $caching = isset($this->config["caching"]) ? $this->config["caching"] : null;

        if (!is_null($caching)) {
            if (isset($caching["cache"]) && $caching["cache"]) {
                if (isset($caching["cacheDir"])) {
                    $this->template->setCacheDir($caching["cacheDir"]);
                }
                $this->template->setCacheLifetime(isset($caching["time"]) ? $caching["time"] : 3600);
                $this->template->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            }
        }
    }

    /**
     * Build the template language based on the template's config
     * @todo Implement some way to pass the language param at the URL through GET request.
     */
    private function buildLanguage() {
        if (isset($this->config["language"])) {
            $localization = PhpI18N::instance();
            $localization->setLocale($this->config["language"]);

            $this->set("localization", $localization);
        }
    }

}

?>
