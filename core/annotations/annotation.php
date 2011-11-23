<?php

App::import("Lib", "addendum/annotations");

class AnnotationFactory {

    private $annotationName;
    private $annotationClass;

    function __construct($annotation, $class) {
        $this->annotationName = $annotation;
        $this->annotationClass = $this->loadAnnotedClass(get_class($class));
    }

    public function loadAnnotedClass($className) {
        if (!class_exists($this->annotationName) && App::path("Core", "annotations/" . Inflector::underscore($this->annotationName) . "_annotation")) {
            App::import("Core", "annotations/" . Inflector::underscore($this->annotationName) . "_annotation");
        }
        if (class_exists($this->annotationName)) {
            return new ReflectionAnnotatedClass($className);
        }
    }

    public function getAnnotationName() {
        return $this->annotationName;
    }

    public function getAnnotationObject($methodName = null) {
        if ($this->hasClassAnnotation()) {
            return $this->annotationClass->getAnnotation($this->annotationName);
        }

        if ($this->hasMethodAnnotation($methodName)) {
            return $this->annotationClass->getMethod($methodName)->getAnnotation($this->annotationName);
        }
    }

    public function hasMethodAnnotation($methodName) {
        if ($this->annotationClass->hasMethod($methodName)) {
            $method = $this->annotationClass->getMethod($methodName);
            return $method->hasAnnotation($this->annotationName);
        }
        return false;
    }

    public function hasClassAnnotation() {
        return $this->annotationClass->hasAnnotation($this->annotationName);
    }

    public function hasAnnotation($methodName = null) {
        if ($this->hasClassAnnotation()) {
            return $this->hasClassAnnotation();
        }

        if ($this->hasMethodAnnotation($methodName)) {
            return $this->hasMethodAnnotation($methodName);
        }
    }

}

?>
