<?php

namespace Easy\View\Engine;

use Easy\Error;
use Easy\Network\Request;
use Easy\Core\App,
    Easy\Core\Config,
    Easy\View\Engine\ITemplateEngine,
    Easy\Utility\Folder;

App::import("Vendors", "smarty/Smarty.class");

class SmartyEngine implements ITemplateEngine
{

    /**
     * Smarty Object
     * @var Smarty 
     */
    protected $template;
    protected $options;
    protected $request;

    function __construct(Request $request)
    {
        $this->request = $request;
        //Instanciate a Smarty object
        $this->template = new \Smarty();
        /*
         * This is to mute all expected erros on Smarty and pass to error handler 
         * TODO: Try to get a better implementation 
         */
        \Smarty::muteExpectedErrors();
        //Build the template directory
        $this->loadOptions();
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function display($layout, $view, $ext = null, $output = true)
    {
        list(, $view) = namespaceSplit($view);
        $ext = empty($ext) ? "tpl" : $ext;

        // If the view not exists...
//        if (!App::path("View", $view, $ext)) {
//            $errors = explode("/", $view);
//            throw new Error\MissingViewException(array(
//                "view" => $errors[1] . "." . $ext,
//            ));
//        }
        // ...display it
        if (!empty($layout)) {
//            if (!App::path("Layout", $layout, $ext)) {
//                throw new Error\MissingLayoutException(array(
//                    "layout" => $layout . $ext,
//                ));
//            }
            return $this->template->fetch("extends:{$layout}.{$ext}|{$view}.{$ext}", null, null, null, $output);
        } else {
            return $this->template->fetch("file:{$view}.{$ext}", null, null, null, $output);
        }
    }

    public function set($var, $value)
    {
        return $this->template->assign($var, $value);
    }

    /**
     * Defines the templates dir
     * @since 0.1.2
     */
    private function loadOptions()
    {
        //Set the options, loaded from the config file
        $this->setOptions(Config::read('View.options'));

        if ($this->request->prefix) {
            $options = array();
            $options["template_dir"]["areas"] = App::path("Areas/{$this->request->prefix}/View/Pages");
            $options["template_dir"]["areasLayouts"] = App::path("Areas/{$this->request->prefix}/View/Layouts");
            $options["template_dir"]["areasElements"] = App::path("Areas/{$this->request->prefix}/View/Elements");
            $this->template->addTemplateDir($options["template_dir"]);
        }

        if (isset($this->options['template_dir'])) {
            $this->template->addTemplateDir($this->options["template_dir"]);
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

    private function checkDir($dir)
    {
        return new Folder($dir, true);
    }

}
