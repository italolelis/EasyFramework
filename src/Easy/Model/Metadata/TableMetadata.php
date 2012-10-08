<?php

namespace Easy\Model\Metadata;

use Easy\Annotations\AnnotationManager;

class TableMetadata
{

    public function getName($model)
    {
        $annotation = new AnnotationManager("TableName", $model);
        //If the method has the anotation Rest
        if ($annotation->hasClassAnnotation()) {
            //Get the anotation object value
            return $annotation->getAnnotationObject()->value;
        }
        return null;
    }

}