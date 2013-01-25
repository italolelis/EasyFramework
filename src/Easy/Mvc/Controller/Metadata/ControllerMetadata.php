<?php

namespace Easy\Mvc\Controller\Metadata;

use Easy\Annotations\AnnotationManager;

class ControllerMetadata
{

    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function getLayout($action)
    {
        $manager = new AnnotationManager("Layout", $this->class);
        $annotation = $manager->getMethodAnnotation($action);
        if ($annotation !== false) {
            return $annotation->value;
        } else {
            return null;
        }
    }

}