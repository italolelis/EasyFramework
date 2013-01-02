<?php

namespace Easy\Annotations\Matcher;

class AnnotationMatcher extends SerialMatcher
{

    protected function build()
    {
        $this->add(new RegexMatcher('@'));
        $this->add(new RegexMatcher('[A-Z][a-zA-Z0-9_]*'));
        $this->add(new AnnotationParametersMatcher);
    }

    protected function process($results)
    {
        return array($results[1], $results[2]);
    }

}
