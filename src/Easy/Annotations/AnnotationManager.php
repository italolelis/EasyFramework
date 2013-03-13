<?php

namespace Easy\Annotations;

use Easy\Core\App;

/**
 * Manage all annotations
 * 
 * @package Easy.Annotations
 */
class AnnotationManager
{

    private $annotationName;
    private $annotedClass;

    /**
     * Crates a AnnotationFactory object
     * @param string $annotation The annotation name
     * @param object $class The class instance which has annotations
     */
    function __construct($annotation, $class)
    {
        $this->annotationName = $annotation;
        if (is_object($class)) {
            $class = get_class($class);
        }
        $this->annotedClass = $this->loadAnnotedClass($class);
    }

    /**
     * Load a class which has annotations within it
     * @param string $className The class name to be loaded
     * @return ReflectionAnnotatedClass The class instance which contains the annotations
     */
    public function loadAnnotedClass($className)
    {
        $annotationClass = App::classname($this->annotationName, 'Annotations/Annotations');
        if (class_exists($annotationClass)) {
            return new ReflectionAnnotatedClass($className);
        }
    }

    /**
     * Verify if the desired method has an annotation
     * @param string $methodName The name of the method
     * @return Boolean True if the annotatios exists in the method 
     */
    public function getMethodAnnotation($methodName)
    {
        if ($this->annotedClass->hasMethod($methodName)) {
            $method = $this->annotedClass->getMethod($methodName);
            return $method->getAnnotation($this->annotationName);
        }
        return null;
    }

    /**
     * Verify if the desired class has an annotation
     * @return Boolean True if the annotatios exists in the class 
     */
    public function getClassAnnotation()
    {
        return $this->annotedClass->getAnnotation($this->annotationName);
    }

    /**
     * Verify if an annotation exists either in class or method
     * @param string $methodName
     * @return \Annotation 
     */
    public function getAnnotation($methodName = null)
    {
        $classAnnotation = $this->getClassAnnotation();
        if ($classAnnotation) {
            return $classAnnotation;
        }

        $methodAnnotation = $this->getMethodAnnotation($methodName);
        if ($methodAnnotation) {
            return $methodAnnotation;
        }

        return null;
    }

}
