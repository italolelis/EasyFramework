<?php

namespace Easy\Rest\Metadata;

use Easy\Annotations\AnnotationManager;

class RestMetadata
{

    public $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function getMethodAnnotation($action)
    {
        $annotation = new AnnotationManager("Method", $this->controller);
        if ($annotation->hasMethodAnnotation($action)) {
            return $annotation->getAnnotationObject($action)->value;
        }
        return null;
    }

    public function getCodeAnnotation($action)
    {
        $annotation = new AnnotationManager("Code", $this->controller);
        if ($annotation->hasMethodAnnotation($action)) {
            return $annotation->getAnnotationObject($action)->value;
        }
        return null;
    }

    public function getFormatAnnotation($action)
    {
        $annotation = new AnnotationManager("Produces", $this->controller);
        if ($annotation->hasMethodAnnotation($action)) {
            return $annotation->getAnnotationObject($action)->value;
        }
        return null;
    }

}
