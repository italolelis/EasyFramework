<?php

namespace Easy\Annotations\Matcher;

class AnnotationArrayValueMatcher extends ParallelMatcher
{

    protected function build()
    {
        $this->add(new AnnotationValueInArrayMatcher);
        $this->add(new AnnotationPairMatcher);
    }

}
