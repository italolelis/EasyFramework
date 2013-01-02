<?php

namespace Easy\Annotations\Matcher;

class AnnotationArrayValuesMatcher extends ParallelMatcher
{

    protected function build()
    {
        $this->add(new AnnotationArrayValueMatcher);
        $this->add(new AnnotationMoreValuesMatcher);
    }

}
