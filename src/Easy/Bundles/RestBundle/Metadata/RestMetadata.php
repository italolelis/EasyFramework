<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\RestBundle\Metadata;

use Doctrine\Common\Annotations\Reader;
use ReflectionMethod;

class RestMetadata
{

    public $class;
    protected $reader;
    protected $annotations = array(
        'code.annotation' => 'Easy\Bundles\RestBundle\Annotation\Code',
        'method.annotation' => 'Easy\Bundles\RestBundle\Annotation\Method',
        'produces.annotation' => 'Easy\Bundles\RestBundle\Annotation\Produces',
        'route.annotation' => 'Easy\Bundles\RestBundle\Annotation\Route'
    );

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function getRouteAnnotation($action)
    {

        $annotation = $this->getAnnotation($action, $this->annotations['route.annotation']);

        if ($annotation !== null) {
            return $annotation->getValue();
        }
        return null;
    }

    public function getMethodAnnotation($action)
    {
        $annotation = $this->getAnnotation($action, $this->annotations['method.annotation']);

        if ($annotation !== null) {
            return $annotation->getValue();
        }
        return null;
    }

    public function getCodeAnnotation($action)
    {
        $annotation = $this->getAnnotation($action, $this->annotations['code.annotation']);

        if ($annotation !== null) {
            return $annotation->getValue();
        }
        return null;
    }

    public function getFormatAnnotation($action)
    {
        $annotation = $this->getAnnotation($action, $this->annotations['produces.annotation']);

        if ($annotation !== null) {
            return $annotation->getValue();
        }
        return null;
    }

    public function getAnnotation($method, $class)
    {
        $reflectionMethod = new ReflectionMethod($this->class, $method);
        $annotation = $this->reader->getMethodAnnotation($reflectionMethod, $class);

        return $annotation;
    }

}
