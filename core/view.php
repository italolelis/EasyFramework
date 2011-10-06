<?php

/**
 *  View é a classe responsável por gerar a saída dos controllers e renderizar a
 *  view e layout correspondente.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
class View extends Object {

    /**
     * Objeto do Smarty
     * @var Smarty 
     */
    private $template;

    /**
     * Objeto que receberá as configurações do template
     * @var mixed 
     */
    private $config;

    function __construct() {
        //Carrega as Configurações do Template
        $this->config = Config::read('template');
        //Constroi o objto Smarty
        $this->template = new Smarty();
        //Informamos o local da view
        $this->buildTemplateDir();
        //Passa o objeto sessão para a view
        $this->buildSession();
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
        return $this->template->display('file:' . $view . "$ext");
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
     * Constroi a sessão do usuário na view
     */
    private function buildSession() {
        //Se o usuário estiver logado
        if (Session::started('usuarios')) {
            //Passa o objeto sessão para a view
            $this->template->assign('sessao', Session::read('usuarios'));
        }
    }

    /**
     * Define as url's da view. Também define quais serão os arquívos padrões de header e footer
     */
    private function buildUrls() {
        //Criamos a variável que contém o caminho do arquivo do header
        $this->template->assign('header', $this->config['header']);
        //Criamos a variável que contém o caminho do arquivo do footer
        $this->template->assign('footer', $this->config['footer']);
        //Pegamos a página que está sendo requisitada, para compararmos com os menus na view
        $this->template->assign('pagina', str_replace("/", "", str_replace("agendamento/", "", $_SERVER['REQUEST_URI'])));
        //Pegamos o mapeamento de url's 
        $this->template->assign('url', $this->config['urls']);
    }

    /**
     * Define os arquívos css do template
     */
    private function buildCss() {
        if (isset($this->config['css']))
            $this->template->assign('css', $this->config['css']);
    }

    /**
     * Define os arquívos javascript do template
     */
    private function buildJs() {
        if (isset($this->config['js']))
            $this->template->assign('js', $this->config['js']);
    }

}

?>
