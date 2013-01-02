<?php

namespace Easy\Annotations\Matcher;

class NestedAnnotationMatcher extends AnnotationMatcher
{

    protected function process($result)
    {
        $builder = new AnnotationsBuilder;
        return $builder->instantiateAnnotation($result[1], $result[2]);
    }

}