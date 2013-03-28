<?php

namespace Easy\Annotations\Matcher;

class AnnotationsMatcher
{

    public function matches($string, &$annotations)
    {
        $annotations = array();
        $annotation_matcher = new AnnotationMatcher();
        while (true) {
            if (preg_match('/\s(?=@)/', $string, $matches, PREG_OFFSET_CAPTURE)) {
                $offset = $matches[0][1] + 1;
                $string = substr($string, $offset);
            } else {
                return; // no more annotations
            }
            if (($length = $annotation_matcher->matches($string, $data)) !== false) {
                $string = substr($string, $length);
                list($name, $params) = $data;
                $annotations[$name][] = $params;
            }
        }
    }

}
