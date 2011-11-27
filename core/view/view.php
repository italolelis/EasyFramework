<?php

App::import("Core", "localization/I18N");

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
class View extends Object {

    /**
     * Smarty Object
     * @var Smarty 
     */
    protected $template;
    protected $config;

    /**
     * Define se a view será renderizada automaticamente
     */
    protected $autoRender = true;

    /**
     * Layout utilizado para exibir a view
     */
    protected $layout = null;

    function __construct() {
        //Carrega as Configurações do Template
        $this->config = Config::read('template');
        //Constroi o objto Smarty
        $this->template = new Smarty();
        //Informamos o local da view
        $this->buildTemplateDir();
        //Passa as váriaveis da url para a view
        $this->buildUrls();
        //Passa os includes para a view
        $this->buildLayouts();
        //Constroi o cache 
        $this->buildCache();
        //Constroi a linguagem do template 
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
        //Se o autorender está habilitado
        if ($this->getAutoRender()) {

            $layout = isset($this->layout) ? $this->layout . '/' : null;

            if (App::path("View", $layout . $view, $ext)) {
                return $this->template->display("file:{$layout}{$view}.{$ext}");
            } else {
                $errors = explode("/", $view);
                throw new MissingViewException("view", array("controller" => $errors[0], "action" => $errors[1]));
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
     * Define the templates dir
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
     * Constroi as urls que serão passadas para a view
     * @since 0.1.2
     */
    private function buildUrls() {
        $this->buildStaticDomain();
        if (isset($this->config['urls'])) {
            $newURls = array();
            $base = Mapper::base() === "/" ? Mapper::domain() : Mapper::base();
            //Pegamos o mapeamento de url's
            foreach ($this->config["urls"] as $key => $value) {
                if (!strstr($value, "http://"))
                    $newURls[$key] = $base . "/" . $value;
            }
            $newURls = array_merge($newURls, array("base" => $base, "atual" => $base . Mapper::atual()));
        }
        $this->set('url', isset($this->config['urls']) ? array_merge($this->config['urls'], $newURls) : "");
    }

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

    private function buildLanguage() {
        if (isset($this->config["language"])) {
            $localization = PhpI18N::instance();
            $localization->setLocale($this->config["language"]);

            $this->set("localization", $localization);
        }
    }

}

?>
