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
        //Passa os css montados para view
        $this->buildCss();
        //Passa os javascripts montados para a view
        $this->buildJs();
    }

    /**
     * Mostra uma view
     * @param string $view o nome do template a ser exibido
     * @param string $ext a extenção do arquivo a ser exibido. O padrão é '.tpl'
     * @return View 
     */
    function display($view, $ext = ".tpl") {
        if ($this->autoRender) {
            $layout = isset($this->layout) ? $this->layout . '/' : null;
            return $this->template->display("file:{$layout}{$view}{$ext}");
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
        //Criamos a variável que contém o caminho do arquivo do header
        $this->template->assign('header', isset($this->config['header']) ? $this->config['header'] : 'header.tpl');
        //Criamos a variável que contém o caminho do arquivo do footer
        $this->template->assign('footer', isset($this->config['footer']) ? $this->config['footer'] : 'footer.tpl');
        //Pegamos a página que está sendo requisitada, para compararmos com os menus na view
        $this->template->assign('pagina', Mapper::atual());
        //Pegamos o mapeamento de url's
        $this->template->assign('url', isset($this->config['urls']) ? $this->config['urls'] : "");
    }

    /**
     * Define os arquívos css do template
     */
    private function buildCss() {
        if (isset($this->config['css']))
            $this->template->assign('css', isset($this->config['css']) ? $this->config['css'] : "");
    }

    /**
     * Define os arquívos javascript do template
     */
    private function buildJs() {
        if (isset($this->config['js']))
            $this->template->assign('js', isset($this->config['js']) ? $this->config['js'] : "");
    }

}

?>
