<?php

/**
 *  View é a classe responsável por gerar a saída dos controllers e renderizar a
 *  view e layout correspondente.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
class View extends Object {

    /**
     * Objeto do Smarty
     * @var Smarty 
     */
    protected $template;
    protected $config;

    /**
     * Define se a view será renderizada automaticamente
     */
    public $autoRender = true;

    /**
     * Layout utilizado para exibir a view
     */
    public $layout = null;

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
        $this->buildIncludes();
        //Constroi o cache 
        $this->buildCache();
    }

    /**
     * Mostra uma view
     * @param string $view o nome do template a ser exibido
     * @param string $ext a extenção do arquivo a ser exibido. O padrão é '.tpl'
     * @return View 
     */
    function display($view, $ext = "tpl") {
        $layout = isset($this->layout) ? $this->layout . '/' : null;
        if (App::path("View", $layout . $view, $ext)) {
            return $this->template->display("file:{$layout}{$view}.{$ext}");
        } else {
            $errors = explode("/", $view);
            throw new MissingViewException("view", array("controller" => $errors[0], "action" => $errors[1]));
        }
    }

    /**
     * Define uma variável que será passada para a view
     * @param string $var o nome da variável que será passada para a view
     * @param mixed $value o valor da varíavel
     */
    function set($var, $value) {
        $this->template->assign($var, $value);
    }

    /**
     * Seta o cahe para uma view específica
     * @param int $time O tempo em milisecundos que o cache vai ficar ativo
     */
    function setCache($time = 3600) {
        $this->template->setCaching(Smarty::CACHING_LIFETIME_SAVED);
        $this->template->setCacheLifetime($time);
    }

    /**
     * Limpa o cache de uma view específica
     * @param int $time O tempo em milisecundos que o cache vai ficar ativo
     */
    function clearCache($template_name) {
        $this->template->clearCache($template_name);
    }

    /**
     * Limpa o cache da aplicação inteira
     * @param int $time O tempo em milisecundos que o cache vai ficar ativo
     */
    function clearAllCache() {
        $this->template->clearAllCache();
    }

    /**
     * Define o local padrão dos templates
     * @since 0.1.2
     */
    private function buildTemplateDir() {
        if (isset($this->config["templateDir"]) && is_array($this->config["templateDir"])) {
            $this->template->setTemplateDir($this->config["templateDir"]);
        } else {
            $this->template->setTemplateDir(array(VIEW_PATH, 'includes' => INCLUDE_PATH));
        }
    }

    /**
     * Constroi as urls que serão passadas para a view
     * @since 0.1.2
     */
    private function buildUrls() {
        if (isset($this->config['urls'])) {
            $newURls = array();
            //Pegamos o mapeamento de url's
            foreach ($this->config["urls"] as $key => $value) {
                if (!strstr($value, "http://"))
                    $newURls[$key] = Mapper::base() . "/" . $value;
            }
            $newURls = array_merge($newURls, array("base" => Mapper::base(), "atual" => Mapper::base() . Mapper::atual()));
        }
        $this->set('url', isset($this->config['urls']) ? array_merge($this->config['urls'], $newURls) : "");
    }

    /**
     * Constroi os includes caso estejam setados na configuração
     * @since 0.1.5
     */
    private function buildIncludes() {
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

}

?>
