<?php

App::import("Lib", "smarty/Smarty.class");

class SmartyEngine implements ITemplateEngine {

    /**
     * Smarty Object
     * @var Smarty 
     */
    protected $template;

    function __construct() {
        //Instanciate a Smarty object
        $this->template = new Smarty();
        //Build the template directory
        $this->buildTemplateDir();
    }

    public function display($view, $ext = "tpl") {
        return $this->template->display("file:{$view}.{$ext}");
    }

    public function set($var, $value) {
        return $this->template->assign($var, $value);
    }

    /**
     * Defines the templates dir
     * @since 0.1.2
     */
    private function buildTemplateDir() {
        $templatesDir = Config::read('View.templatesDir');
        if (!is_null($templatesDir) && is_array($templatesDir)) {
            $this->template->setTemplateDir($this->config["templateDir"]);
        } else {
            $this->template->setTemplateDir(array(
                'views' => App::path("View"),
                'layouts' => App::path("Layout"),
                'elements' => App::path("Layout/Elements")
            ));
        }
    }

}

?>
