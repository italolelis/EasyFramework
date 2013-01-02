<?php

namespace Easy\Annotations\Matcher;

class ConstantMatcher extends RegexMatcher
{

    private $constant;

    public function __construct($regex, $constant)
    {
        parent::__construct($regex);
        $this->constant = $constant;
    }

    protected function process($matches)
    {
        return $this->constant;
    }

}
