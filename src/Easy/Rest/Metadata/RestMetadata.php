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
        $manager = new AnnotationManager("Method", $this->controller);
        $annotation = $manager->getMethodAnnotation($action);

        if (!empty($annotation)) {
            return $annotation->value;
        }
        return null;
    }

    public function getCodeAnnotation($action)
    {
        $manager = new AnnotationManager("Code", $this->controller);
        $annotation = $manager->getMethodAnnotation($action);
        if (!empty($annotation)) {
            return $annotation->value;
        }
        return null;
    }

    public function getFormatAnnotation($action)
    {
        $manager = new AnnotationManager("Produces", $this->controller);
        $annotation = $manager->getMethodAnnotation($action);
        if (!empty($annotation)) {
            return $annotation->value;
        }
        return null;
    }

}
