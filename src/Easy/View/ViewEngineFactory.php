<?php

namespace Easy\View;

use Easy\Core\App;
use Easy\Utility\Inflector;
use Easy\View\Exception\MissingEngineException;

class ViewEngineFactory
{

    public function build($type)
    {
        $engine = Inflector::camelize($type);
        $viewEngineClass = App::classname($engine, 'View/Engine', 'Engine');

        if (class_exists($viewEngineClass)) {
            //we pass the request to help find wich area we are using
            return new $viewEngineClass($this->controller->request);
        }
        throw new MissingEngineException(__("The engine %s doesn't exists.", $engine));
    }

}