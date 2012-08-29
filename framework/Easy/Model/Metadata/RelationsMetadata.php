<?php

namespace Easy\Model\Metadata;

use Easy\Annotations\AnnotationManager;

class RelationsMetadata
{

    public function getHasOne($model)
    {
        $annotation = new AnnotationManager("HasOne", $model);
        //If the method has the anotation Rest
        if ($annotation->hasClassAnnotation()) {
            //Get the anotation object value
            return $annotation->getAnnotationObject()->value;
        }
        return null;
    }

    public function getHasMany($model)
    {
        $annotation = new AnnotationManager("HasMany", $model);
        //If the method has the anotation Rest
        if ($annotation->hasClassAnnotation()) {
            //Debugger::dump($annotation->getAnnotationObject()->value);
            //Get the anotation object value
            return $annotation->getAnnotationObject()->value;
        }
        return null;
    }

    public function getBelongsTo($model)
    {
        $annotation = new AnnotationManager("BelongsTo", $model);
        //If the method has the anotation Rest
        if ($annotation->hasClassAnnotation()) {
            //Get the anotation object value
            return $annotation->getAnnotationObject()->value;
        }
        return null;
    }

    public function getHasAndBelongsToMany($model)
    {
        $annotation = new AnnotationManager("HasAndBelongsToMany", $model);
        //If the method has the anotation Rest
        if ($annotation->hasClassAnnotation()) {
            //Get the anotation object value
            return $annotation->getAnnotationObject()->value;
        }
        return null;
    }

}