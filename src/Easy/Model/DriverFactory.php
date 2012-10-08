<?php

namespace Easy\Model;

use Easy\Core\App;
use Easy\Core\Object;
use Easy\Error\MissingDatasourceException;
use Easy\Utility\Inflector;

class DriverFactory extends Object
{

    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function build($type = null)
    {
        $type = Inflector::camelize($type);
        $className = App::classname($type, "Model/Drivers");

        if (!class_exists($className)) {
            throw new MissingDatasourceException(array(
                'class' => $className
            ));
        }
        $class = new $className($this->config);
        return $class;
    }

}