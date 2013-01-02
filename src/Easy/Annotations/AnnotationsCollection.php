<?php

namespace Easy\Annotations;

use Easy\Core\App;

class AnnotationsCollection
{

    private $annotations;

    public function __construct($annotations)
    {
        $this->annotations = $annotations;
    }

    public function hasAnnotation($class)
    {
        $class = Addendum::resolveClassName($class);
        $class = App::classname($class, "Annotations/Annotations");
        return isset($this->annotations[$class]);
    }

    public function getAnnotation($class)
    {
        $class = Addendum::resolveClassName($class);
        $class = App::classname($class, "Annotations/Annotations");
        return isset($this->annotations[$class]) ? end($this->annotations[$class]) : false;
    }

    public function getAnnotations()
    {
        $result = array();
        foreach ($this->annotations as $instances) {
            $result[] = end($instances);
        }
        return $result;
    }

    public function getAllAnnotations($restriction = false)
    {
        $restriction = Addendum::resolveClassName($restriction);
        $class = App::classname($restriction, "Annotations/Annotations");
        $result = array();
        foreach ($this->annotations as $class => $instances) {
            if (!$restriction || $restriction == $class) {
                $result = array_merge($result, $instances);
            }
        }
        return $result;
    }

}
