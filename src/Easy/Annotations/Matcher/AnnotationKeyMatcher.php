<?php

namespace Easy\Annotations\Matcher;

class AnnotationKeyMatcher extends ParallelMatcher
{

    protected function build()
    {
        $this->add(new RegexMatcher('[a-zA-Z][a-zA-Z0-9_]*'));
        $this->add(new AnnotationStringMatcher);
        $this->add(new AnnotationIntegerMatcher);
    }

}
