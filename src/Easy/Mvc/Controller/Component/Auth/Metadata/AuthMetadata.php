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
        $annotation = new AnnotationManager("Authorized", $this->class);
        //If the method has the anotation Rest
        if ($annotation->hasAnnotation($action)) {
            //Get the anotation object
            $roles = $annotation->getAnnotationObject($action);
            return (array) $roles->roles;
        }
        return null;
    }

    public function isGuest($action)
    {
        $annotation = new AnnotationManager("Guest", $this->class);
        //If the method has the anotation Rest
        return $annotation->hasAnnotation($action);
    }

}