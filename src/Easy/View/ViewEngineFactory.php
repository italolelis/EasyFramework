<?php

namespace Easy\View;

use Easy\Core\App;
use Easy\Network\Request;
use Easy\Utility\Inflector;
use Easy\View\Exception\MissingEngineException;

class ViewEngineFactory
{

    /**
     * @var Request The request object
     */
    protected $request;

    function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function build($type, $options)
    {
        $engine = Inflector::camelize($type);
        $viewEngineClass = App::classname($engine, 'View/Engine', 'Engine');

        if (class_exists($viewEngineClass)) {
            //we pass the request to help find wich area we are using
            return new $viewEngineClass($this->request, $options);
        }
        throw new MissingEngineException(__("The engine %s doesn't exists.", $engine));
    }

}