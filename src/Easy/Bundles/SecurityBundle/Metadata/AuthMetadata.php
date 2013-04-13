<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SecurityBundle\Metadata;

use Doctrine\Common\Annotations\Reader;
use ReflectionMethod;

class AuthMetadata
{

    protected $class;
    protected $reader;
    protected $annotations = array(
        'guest.annotation' => 'Easy\Bundles\SecurityBundle\Annotation\Guest',
        'authorized.annotation' => 'Easy\Bundles\SecurityBundle\Annotation\Authrized'
    );

    public function __construct($class, Reader $reader)
    {
        $this->class = $class;
        $this->reader = $reader;
    }

    public function getAuthorized($action)
    {
        $annotation = $this->getAnnotation($action, $this->annotations['authorized.annotation']);
        if ($annotation !== null) {
            return (array) $annotation->getRoles();
        } else {
            return null;
        }
    }

    public function isGuest($action)
    {
        $annotation = $this->getAnnotation($action, $this->annotations['guest.annotation']);
        //If the method has the anotation Rest
        return (bool) $annotation;
    }

    public function getAnnotation($method, $class)
    {
        $reflectionMethod = new ReflectionMethod($this->class, $method);
        $annotation = $this->reader->getMethodAnnotation($reflectionMethod, $class);

        return $annotation;
    }

}