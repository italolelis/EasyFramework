<?php

namespace Easy\Annotations\Matcher;

class AnnotationMoreValuesMatcher extends SimpleSerialMatcher
{

    protected function build()
    {
        $this->add(new AnnotationArrayValueMatcher);
        $this->add(new RegexMatcher('\s*,\s*'));
        $this->add(new AnnotationArrayValuesMatcher);
    }

    protected function match($string, &$value)
    {
        $result = parent::match($string, $value);
        return $result;
    }

    public function process($parts)
    {
        return array_merge($parts[0], $parts[2]);
    }

}
