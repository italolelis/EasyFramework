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
    private $template;

    /**
     * Define se a view será renderizada automaticamente
     */
    public $autoRender = true;

    /**
     * Layout utilizado para exibir a view
     */
    public $layout = null;

    /**
     * Objeto que receberá as configurações do template
     */
    private $config;

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
            $this->error("view", array("controller" => $errors[0], "action" => $errors[1]));
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
     * Define o local padrão dos templates
     * @return Smarty 
     */
    private function buildTemplateDir() {
        return $this->template->setTemplateDir(array(VIEW_PATH, 'includes' => INCLUDE_PATH));
    }

    /**
     * Define as url's da view. Também define quais serão os arquívos padrões de header e footer
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

    private function buildIncludes() {
        if (isset($this->config["layout"])) {
            if (is_array($this->config["layout"])) {
                foreach ($this->config["layout"] as $key => $value) {
                    $this->set($key, $value);
                }
            }
        }
    }

}

?>
