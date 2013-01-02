<?php

namespace Easy\Annotations;

use ReflectionClass;

class ReflectionAnnotatedClass extends ReflectionClass
{

    private $annotations;

    public function __construct($class)
    {
        parent::__construct($class);
        $this->annotations = $this->createAnnotationBuilder()->build($this);
    }

    public function hasAnnotation($class)
    {
        return $this->annotations->hasAnnotation($class);
    }

    public function getAnnotation($annotation)
    {
        return $this->annotations->getAnnotation($annotation);
    }

    public function getAnnotations()
    {
        return $this->annotations->getAnnotations();
    }

    public function getAllAnnotations($restriction = false)
    {
        return $this->annotations->getAllAnnotations($restriction);
    }

    public function getConstructor()
    {
        return $this->createReflectionAnnotatedMethod(parent::getConstructor());
    }

    public function getMethod($name)
    {
        return $this->createReflectionAnnotatedMethod(parent::getMethod($name));
    }

    public function getMethods($filter = -1)
    {
        $result = array();
        foreach (parent::getMethods($filter) as $method) {
            $result[] = $this->createReflectionAnnotatedMethod($method);
        }
        return $result;
    }

    public function getProperty($name)
    {
        return $this->createReflectionAnnotatedProperty(parent::getProperty($name));
    }

    public function getProperties($filter = -1)
    {
        $result = array();
        foreach (parent::getProperties($filter) as $property) {
            $result[] = $this->createReflectionAnnotatedProperty($property);
        }
        return $result;
    }

    public function getInterfaces()
    {
        $result = array();
        foreach (parent::getInterfaces() as $interface) {
            $result[] = $this->createReflectionAnnotatedClass($interface);
        }
        return $result;
    }

    public function getParentClass()
    {
        $class = parent::getParentClass();
        return $this->createReflectionAnnotatedClass($class);
    }

    protected function createAnnotationBuilder()
    {
        return new AnnotationsBuilder();
    }

    private function createReflectionAnnotatedClass($class)
    {
        return ($class !== false) ? new ReflectionAnnotatedClass($class->getName()) : false;
    }

    private function createReflectionAnnotatedMethod($method)
    {
        return ($method !== null) ? new ReflectionAnnotatedMethod($this->getName(), $method->getName()) : null;
    }

    private function createReflectionAnnotatedProperty($property)
    {
        return ($property !== null) ? new ReflectionAnnotatedProperty($this->getName(), $property->getName()) : null;
    }

}
