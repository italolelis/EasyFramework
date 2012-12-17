<?php

namespace Easy\Annotations\Matcher;

class AnnotationValueInArrayMatcher extends AnnotationValueMatcher
{

    public function process($value)
    {
        return array($value);
    }

}
