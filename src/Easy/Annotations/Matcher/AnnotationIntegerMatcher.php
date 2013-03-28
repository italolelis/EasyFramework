<?php

namespace Easy\Annotations\Matcher;

class AnnotationIntegerMatcher extends RegexMatcher
{

    public function __construct()
    {
        parent::__construct("-?[0-9]*");
    }

    protected function process($matches)
    {
        return (int) $matches[0];
    }

}
