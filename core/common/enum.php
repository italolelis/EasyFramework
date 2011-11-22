<?php

/**
 * enum() - a function for generating type safe, iterable, singleton enumerations.
 *
 * enum() will create count($args) + 2 classes in the global namespace. It creates
 * an abstract base class with the name $base_class. This class is given static
 * methods with the names of each of the enum values.
 *
 * A class is created for each enum value that extends $base_class. Each of these
 * classes are singletons and contain a single private field: a string containing
 * the name of the class.
 *
 * Finally, an iterator is created (accessible via the ::iterator() method on
 * $base_class). This method returns a singleton iterator for the enum, usable
 * with foreach.
 *
 * C Example:
 *     typedef enum { Male, Female } Gender;
 *     Gender g = Male;
 *     switch (g) {
 *     case Male: printf("it's a dude.\n"); break;
 *     case Female: printf("it's a lady\n"); break;
 *     }
 *
 * PHP Equivalent:
 *     enum('Gender', array('Male', 'Female'));
 *     $g = Gender::Male();
 *     switch ($g) {
 *     case Gender::Male(): echo 'it\'s a dude', PHP_EOL; break;
 *     case Gender::Female(): echo 'it\'s a lady', PHP_EOL; break;
 *     }
 * 
 * You can also extend Enums to more specif values. You may have care makes and
 * would like specific models:
 *
 *     enum('CarType', array('Audi', 'BMW', 'Mercedes'));
 *     enum('AudiType extends CarType', array('A4', SR6'));
 *
 * Looping through will only include the immediate decendents of an enum type
 *
 *     php% foreach (CarType::iterator() as $type) { echo $type, ' '; }
 *     => Audi, 'BMW', 'Mercedes'
 *     php% foreach (AudiType::iterator() as $type) { echo $type, ' '; }
 *     => A4, SR6
 *
 * By default, the values of each of the enums are integers 0 through count - 1,
 * like C's default enumeration values. You can however, specify any scalar value,
 * by setting the key of the array of enum values. For example, if you wanted to
 * use the strings 'm' and 'f' as the values for the respective values in
 * the Gender enumeration, you would call enum with the following parameters:
 *
 *     enum('Gender', array('f' => 'Female', 'm' => 'Male'));
 *
 * Values are limited to numeric and string data. If future versions of PHP
 * support array keys of additional data types, enum() automatically support those
 * as well.
 *
 * You can compare by value or class
 *     $g = Gender::male();
 *     $g === Gender::male();   # true
 *     $g === Male::instance(); # true
 *     $g instanceof Gender;    # true
 *     $g instanceof Male;      # true
 *     $g === Gender::Female()  # false
 *
 * And you can use any of classes generated as type hints in function signatures
 *     class Person {
 *         private $gender;
 *         public function __construct(Gender $g) { $this->gender = $g; }
 *         public function gender() { return $this->gender }
 *     }
 *     function for_guys_only(Male $gender) {
 *         // runtime triggers error on instanceof Female
 *     }
 *
 * @author Jonathan Hohle, http://hohle.net
 * @since 5.June.2008
 * @license MIT License
 *  
 * @param $base_class enumeration name
 * @param $args array of enum values
 * @return nothing
 */
function enum($base_class, array $args) {
    $class_parts = preg_split('/\s+/', $base_class);
    $base_class_name = array_shift($class_parts);
    $enums = array();

    foreach ($args as $k => $enum) {
        $static_method = 'public static function ' . $enum .
                '() { return ' . $enum . '::instance(); }';
        $enums[$static_method] = '
            class ' . $enum . ' extends ' . $base_class_name . '{
                private static $instance = null;
                protected $value = "' . addcslashes($k, '\\') . '";
                private function __construct() {}
                private function __clone() {}
                public static function instance() {
                    if (self::$instance === null) { self::$instance = new self(); }
                    return self::$instance;
                }
            }';
    }

    $base_class_declaration = sprintf('
        abstract class %s {
            protected $value = null;
            %s
            public static function iterator() { return %sIterator::instance(); }
            public function value() { return $this->value; }
            public function __toString() { return (string) $this->value; }
        };', $base_class, implode(PHP_EOL, array_keys($enums)), $base_class_name);

    $iterator_declaration = sprintf('
        class %sIterator implements Iterator {
            private static $instance = null;
            private $values = array(\'%s\');
            private function __construct() {}
            private function __clone() {}
            public static function instance() {
                if (self::$instance === null) { self::$instance = new self(); }
                return self::$instance;
            }
            public function current() {
                $value = current($this->values);
                if ($value === false) { return false; }
                return call_user_func(array(\'%s\', $value));
            }
            public function key() { return key($this->values); }
            public function next() {
                next($this->values);
                return $this->current();
            }
            public function rewind() { return reset($this->values); }
            public function valid() { return (bool) $this->current(); }
        };', $base_class_name, implode('\',\'', $args), $base_class_name);

    eval($base_class_declaration);
    eval($iterator_declaration);
    eval(implode(PHP_EOL, $enums));
}

?>
