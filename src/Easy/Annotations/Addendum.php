<?php

namespace Easy\Annotations;

use ReflectionClass;

class Addendum
{

    private static $rawMode;
    private static $ignore;
    private static $classnames = array();
    private static $annotations = false;

    public static function getDocComment($reflection)
    {
        if (self::checkRawDocCommentParsingNeeded()) {
            $docComment = new DocComment();
            return $docComment->get($reflection);
        } else {
            return $reflection->getDocComment();
        }
    }

    /** Raw mode test */
    private static function checkRawDocCommentParsingNeeded()
    {
        if (self::$rawMode === null) {
            $reflection = new ReflectionClass('Easy\Annotations\Addendum');
            $method = $reflection->getMethod('checkRawDocCommentParsingNeeded');
            self::setRawMode($method->getDocComment() === false);
        }
        return self::$rawMode;
    }

    public static function setRawMode($enabled = true)
    {
        self::$rawMode = $enabled;
    }

    public static function resetIgnoredAnnotations()
    {
        self::$ignore = array();
    }

    public static function ignores($class)
    {
        return isset(self::$ignore[$class]);
    }

    public static function ignore()
    {
        foreach (func_get_args() as $class) {
            self::$ignore[$class] = true;
        }
    }

    public static function resolveClassName($class)
    {
        if (isset(self::$classnames[$class]))
            return self::$classnames[$class];
        $matching = array();
        foreach (self::getDeclaredAnnotations() as $declared) {
            if ($declared == $class) {
                $matching[] = $declared;
            } else {
                $pos = strrpos($declared, "_$class");
                if ($pos !== false && ($pos + strlen($class) == strlen($declared) - 1)) {
                    $matching[] = $declared;
                }
            }
        }
        $result = null;
        switch (count($matching)) {
            case 0: $result = $class;
                break;
            case 1: $result = $matching[0];
                break;
            default: trigger_error("Cannot resolve class name for '$class'. Possible matches: " . join(', ', $matching), E_USER_ERROR);
        }
        self::$classnames[$class] = $result;
        return $result;
    }

    private static function getDeclaredAnnotations()
    {
        if (!self::$annotations) {
            self::$annotations = array();
            foreach (get_declared_classes() as $class) {
                if (is_subclass_of($class, 'Easy\Annotations\Addendum') || $class == 'Annotation')
                    self::$annotations[] = $class;
            }
        }
        return self::$annotations;
    }

}
