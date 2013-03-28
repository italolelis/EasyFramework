<?php

namespace Easy\Annotations\Matcher;

class SerialMatcher extends CompositeMatcher
{

    protected function match($string, &$value)
    {
        $results = array();
        $total_length = 0;
        foreach ($this->matchers as $matcher) {
            if (($length = $matcher->matches($string, $result)) === false)
                return false;
            $total_length += $length;
            $results[] = $result;
            $string = substr($string, $length);
        }
        $value = $this->process($results);
        return $total_length;
    }

    protected function process($results)
    {
        return implode('', $results);
    }

}
