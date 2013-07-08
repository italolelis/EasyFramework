<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller\Metadata;

use Doctrine\Common\Annotations\Reader;
use ReflectionMethod;

class ControllerMetadata
{

    protected $class;
    protected $reader;
    protected $annotations = array(
        'layout.annotation' => 'Easy\Mvc\Annotation\Template'
    );

    public function __construct($class, Reader $reader)
    {
        $this->class = $class;
        $this->reader = $reader;
    }

    public function getTemplateAnnotation($action)
    {
        $annotation = $this->getAnnotation($action, $this->annotations['layout.annotation']);

        if ($annotation !== null) {
            return $annotation;
        }
        return false;
    }

    public function getAnnotation($method, $class)
    {
        $reflectionMethod = new ReflectionMethod($this->class, $method);
        $annotation = $this->reader->getMethodAnnotation($reflectionMethod, $class);

        return $annotation;
    }

}