<?php

App::import("Vendors", "smarty/Smarty.class");
App::uses('Folder', 'Utility');

class SmartyEngine implements ITemplateEngine {

    /**
     * Smarty Object
     * @var Smarty 
     */
    protected $template;
    protected $options;

    function __construct() {
        //Instanciate a Smarty object
        $this->template = new Smarty();
        /*
         * This is to mute all expected erros on Smarty and pass to error handler 
         * TODO: Try to get a better implementation 
         */
        Smarty::muteExpectedErrors();
        //Build the template directory
        $this->loadOptions();
    }

    public function getOptions() {
        return $this->options;
    }

    public function setOptions($options) {
        $this->options = $options;
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
    private function loadOptions() {
        //Set the options, loaded from the config file
        $this->setOptions(Config::read('View.options'));

        if (isset($this->options['template_dir'])) {
            $this->template->setTemplateDir($this->options["template_dir"]);
        } else {
            $this->template->setTemplateDir(array(
                'views' => App::path("View"),
                'layouts' => App::path("Layout"),
                'elements' => App::path("Element")
            ));
        }
        if (isset($this->options['compile_dir'])) {
            $this->checkDir($this->options["compile_dir"]);
            $this->template->setCompileDir($this->options["compile_dir"]);
        }

        if (isset($this->options['cache_dir'])) {
            $this->checkDir($this->options["cache_dir"]);
            $this->template->setCacheDir($this->options["cache_dir"]);
        }

        if (isset($this->options['cache'])) {
            $this->template->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $this->template->setCacheLifetime($this->options['cache']['lifetime']);
        }
    }

    private function checkDir($dir) {
        return new Folder($dir, true);
    }

}
