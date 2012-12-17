<?php

namespace Easy\Annotations\Matcher;

class AnnotationArrayMatcher extends ParallelMatcher
{

    protected function build()
    {
        $this->add(new ConstantMatcher('{}', array()));
        $values_matcher = new SimpleSerialMatcher(1);
        $values_matcher->add(new RegexMatcher('\s*{\s*'));
        $values_matcher->add(new AnnotationArrayValuesMatcher);
        $values_matcher->add(new RegexMatcher('\s*}\s*'));
        $this->add($values_matcher);
    }

}
