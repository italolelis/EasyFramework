<?php

namespace Easy\Annotations\Matcher;

class AnnotationPairMatcher extends SerialMatcher
{

    protected function build()
    {
        $this->add(new AnnotationKeyMatcher);
        $this->add(new RegexMatcher('\s*=\s*'));
        $this->add(new AnnotationValueMatcher);
    }

    protected function process($parts)
    {
        return array($parts[0] => $parts[2]);
    }

}
