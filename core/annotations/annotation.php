<?php

App::import("Lib", "addendum/annotations");

class AnnotationFactory {

    private $annotationName;
    private $annotationClass = array();

    function __construct($annotation, $class) {
        $this->annotationName = $annotation;
        $this->annotationClass = $this->loadAnnotedClass($class);
    }

    public function loadAnnotedClass($className) {

        if (!class_exists($this->annotationName) && App::path("Core", "annotations/" . Inflector::underscore($this->annotationName) . "_annotation")) {
            App::import("Core", "annotations/" . Inflector::underscore($this->annotationName) . "_annotation");
        }
        if (class_exists($this->annotationName)) {
            return new ReflectionAnnotatedClass(get_class($className));
        }
    }

    public function getAnnotation() {
        return $this->annotationClass;
    }

    public function hasAnnotation($methodName) {

        if ($this->annotationClass->hasMethod($methodName)) {
            $method = $this->annotationClass->getMethod($methodName);

            if ($method->hasAnnotation($this->annotationName)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

}

?>
