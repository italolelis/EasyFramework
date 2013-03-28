<?php

namespace Easy\Annotations\Matcher;

class AnnotationValueMatcher extends ParallelMatcher
{

    protected function build()
    {
        $this->add(new ConstantMatcher('true', true));
        $this->add(new ConstantMatcher('false', false));
        $this->add(new ConstantMatcher('TRUE', true));
        $this->add(new ConstantMatcher('FALSE', false));
        $this->add(new ConstantMatcher('NULL', null));
        $this->add(new ConstantMatcher('null', null));
        $this->add(new AnnotationStringMatcher);
        $this->add(new AnnotationNumberMatcher);
        $this->add(new AnnotationArrayMatcher);
        $this->add(new AnnotationStaticConstantMatcher);
        $this->add(new NestedAnnotationMatcher);
    }

}
