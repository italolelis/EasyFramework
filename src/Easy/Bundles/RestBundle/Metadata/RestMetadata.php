<?php

namespace Easy\Bundles\RestBundle\Metadata;

use Easy\Annotations\AnnotationManager;

class RestMetadata
{

    public $controller;

    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function getRouteAnnotation($action)
    {
        $manager = new AnnotationManager("Route", $this->controller);
        $annotation = $manager->getMethodAnnotation($action);

        if (!empty($annotation)) {
            return $annotation->value;
        }
        return null;
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

    public function isAjax($action)
    {
        $annotation = new AnnotationManager("Ajax", $this->controller);
        return (bool) $annotation->getAnnotation($action);
    }

}
