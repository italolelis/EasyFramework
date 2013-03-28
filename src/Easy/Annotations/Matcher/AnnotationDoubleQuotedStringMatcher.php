<?php

namespace Easy\Annotations\Matcher;

class AnnotationDoubleQuotedStringMatcher extends RegexMatcher
{

    public function __construct()
    {
        parent::__construct('"([^"]*)"');
    }

    protected function process($matches)
    {
        return $matches[1];
    }

}
