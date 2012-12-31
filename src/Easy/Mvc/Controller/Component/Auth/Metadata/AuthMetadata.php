<?php

namespace Easy\Mvc\Controller\Component\Auth\Metadata;

use Easy\Annotations\AnnotationManager;

class AuthMetadata
{

    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function getAuthorized($action)
    {
        $manager = new AnnotationManager("Authorized", $this->class);
        $annotation = $manager->getAnnotation($action);
        if ($annotation !== null) {
            return (array) $annotation->value;
        } else {
            return null;
        }
    }

    public function isGuest($action)
    {
        $annotation = new AnnotationManager("Guest", $this->class);
        //If the method has the anotation Rest
        return (bool) $annotation->getAnnotation($action);
    }

}