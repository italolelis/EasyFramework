<?php

App::import("Lib", "addendum/annotations");

class AnnotationManager {

    private $annotationName;
    private $annotationClass;

    /**
     * Crates a AnnotationFactory object
     * @param string $annotation The annotation name
     * @param object $class The class instance which has annotations
     */
    function __construct($annotations, $class) {
        $this->annotationName = $annotations;
        $this->annotationClass = $this->loadAnnotedClass(get_class($class));
    }

    /**
     * Load a class which has annotations within it
     * @param string $className The class name to be loaded
     * @return ReflectionAnnotatedClass The class instance which contains the annotations
     */
    public function loadAnnotedClass($className) {
        if (!class_exists($this->annotationName) && App::path("Core", "Annotations/" . strtolower($this->annotationName) . "_annotation")) {
            App::import("Core", "Annotations/" . strtolower($this->annotationName) . "_annotation");
        }
        if (class_exists($this->annotationName)) {
            return new ReflectionAnnotatedClass($className);
        }
    }

    /**
     * Get the annotation's name
     * @return string 
     */
    public function getAnnotationName() {
        return $this->annotationName;
    }

    /**
     * Get the Annotation class instance
     * @param string $methodName Method's name which will be verified
     * @return object 
     */
    public function getAnnotationObject($methodName = null) {
        if ($this->hasClassAnnotation()) {
            return $this->annotationClass->getAnnotation($this->annotationName);
        }

        if ($this->hasMethodAnnotation($methodName)) {
            return $this->annotationClass->getMethod($methodName)->getAnnotation($this->annotationName);
        }
    }

    /**
     * Verify if the desired method has an annotation
     * @param string $methodName The name of the method
     * @return Boolean True if the annotatios exists in the method 
     */
    public function hasMethodAnnotation($methodName) {
        if ($this->annotationClass->hasMethod($methodName)) {
            $method = $this->annotationClass->getMethod($methodName);
            return $method->hasAnnotation($this->annotationName);
        }
        return false;
    }

    /**
     * Verify if the desired class has an annotation
     * @return Boolean True if the annotatios exists in the class 
     */
    public function hasClassAnnotation() {
        return $this->annotationClass->hasAnnotation($this->annotationName);
    }

    /**
     * Verify if an annotation exists either in class or method
     * @param type $methodName
     * @return type 
     */
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
