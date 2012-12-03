<?php

namespace Easy\Generics;

use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;


/**
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-03-03
 * @version 1.1 2009-03-04
 */
class Type implements IEquatable
{

    const INT = '`int`';
    const DOUBLE = '`double`';
    const NUMBER = '`number`';
    const BOOL = '`bool`';
    const ARRAY_ = '`array`';
    const STRING = '`string`';
    const OBJECT = '`object`';
    const RESOURCE = '`resource`';

    private static $functions = array(
        Type::INT => 'is_int',
        Type::DOUBLE => 'is_double',
        Type::NUMBER => 'is_numeric',
        Type::BOOL => 'is_bool',
        Type::ARRAY_ => 'is_array',
        Type::STRING => 'is_string',
        Type::OBJECT => 'is_object',
        Type::RESOURCE => 'is_resource'
    );

    public static function GetType($string, $throwExceptionOnError = true)
    {
        $m = array();
        preg_match_all('/[\w`]+|[<,>]/', $string, $m);
        $m = $m[0];
        $s = 0;
        try {
            $res = self::generateType($m, $s, true);
        } catch (Exception $ex) {
            if ($throwExceptionOnError == true) {
                throw $ex;
            } else {
                return null;
            }
        }
        return $res;
    }

    public static function TypeOf($obj)
    {
        $genericArguments = array();
        if (is_object($obj)) {
            $name = get_class($obj);
            if ($obj instanceof IGeneric) {
                $genericArguments = $obj->GetTypes();
            }
        } else {
            $name = gettype($obj);
            if ($name == 'integer')
                $name = 'int';
            if ($name == 'boolean')
                $name = 'bool';
            $name = '`' . $name . '`';
        }
        return new Type($name, $genericArguments);
    }

    private static function generateType(array &$stringArray, &$pos)
    {
        $name = $stringArray[$pos];
        if (self::isType($name) == false)
            throw new InvalidArgumentException("The type is invalid1!");

        $genericTypes = array();
        $inner = false;
        for ($pos++; $pos < count($stringArray); $pos++) {
            $cur = $stringArray[$pos];
            switch ($cur) {
                case '<':
                    $inner = true;
                    $genericTypes[] = self::generateType($stringArray, ++$pos);
                    break;
                case ',':
                    if ($inner == true) {
                        $genericTypes[] = self::generateType($stringArray, ++$pos);
                        break;
                    } else {
                        $pos--;
                        break 2;
                    }
                case '>':
                    if ($inner == false) {
                        $pos--;
                    }
                    break 2;
                default:
                    throw new InvalidArgumentException("The type is invalid2!");
            }
        }

        $genericTypesCount = count($genericTypes);
        if (class_exists($name)) {
            $class = new ReflectionClass($name);
            if ($class->implementsInterface('IGeneric')) {
                $method = $class->getMethod('NumberOfTypes');
                $num = $method->invoke(null);
                if ($num != $genericTypesCount) {
                    throw new RuntimeException("The generic type $name needs a different number of types ($num)!");
                }
            } else {
                if ($genericTypesCount != 0)
                    throw new RuntimeException("The type $name is not generic!");
            }
        } else {
            if ($genericTypesCount != 0)
                throw new RuntimeException("The type $name is not generic!");
        }

        return new Type($name, $genericTypes);
    }

    private static function isType($type)
    {
        if (is_string($type)) {
            if (array_key_exists($type, self::$functions))
                return true;
            if (class_exists($type, true))
                return true;
            if (interface_exists($type, true))
                return true;
        }
        return false;
    }

    private $reflection;
    private $typeName;
    private $genericsArray;

    private function __construct($name, array $genericArguments = array())
    {
        $this->typeName = $name;
        if (class_exists($name) || interface_exists($name)) {
            $this->reflection = new ReflectionClass($name);
        } else {
            $this->reflection = null;
        }
        $this->genericsArray = $genericArguments;
    }

    public function IsGeneric()
    {
        return count($this->genericsArray) > 0;
    }

    public function IsInterface()
    {
        if ($this->reflection == null)
            return false;
        return $this->reflection->isInterface();
    }

    public function IsClass()
    {
        if ($this->reflection == null)
            return false;
        return $this->reflection->isInterface() == false;
    }

    public function IsPrimitive()
    {
        return $this->reflection == null;
    }

    public function IsAbstract()
    {
        return $this->reflection->isAbstract();
    }

    public function IsFinal()
    {
        return $this->reflection->isFinal();
    }

    public function IsSubclassOf(Type $type)
    {
        if ($this->IsPrimitive() || $type->IsPrimitive())
            return false;
        if ($type->IsGeneric())
            return false;
        if ($this->reflection->isSubclassOf($type->reflection) == false)
            return false;
        return true;
    }

    public function ImplementsInterface(string $name)
    {
        return $this->reflection->implementsInterface($name);
    }

    public function Name()
    {
        return $this->typeName;
    }

    public function FullName()
    {
        $name = $this->typeName;
        if ($this->IsGeneric()) {
            $name .= '<';
            foreach ($this->genericsArray AS $v) {
                $name .= $v->FullName() . ',';
            }
            $name = substr($name, 0, -1);
            $name .= '>';
        }
        return $name;
    }

    public function GetGenericTypesCount()
    {
        return count($this->genericsArray);
    }

    public function GetGenericTypes()
    {
        return $this->genericsArray;
    }

    public function IsItemFromType($item)
    {
        $type = $this->typeName;
        if ($this->IsPrimitive()) {
            if ($type == Type::NUMBER) {
                if (is_string($item) == true)
                    return false;
            }
            $func = self::$functions[$type];
            return $func($item);
        }
        $itemType = Type::TypeOf($item);
        if ($this->Equals($itemType))
            return true;
        if ($itemType->IsSubclassOf($this))
            return true;
        return false;
    }

    public function equals($obj)
    {
        if (!($obj instanceof Type))
            return false;
        if ($obj->Name() != $this->Name())
            return false;
        $genericsCount = $this->GetGenericTypesCount();
        if ($obj->GetGenericTypesCount() != $genericsCount)
            return false;
        if ($genericsCount > 0) {
            $arr1 = $this->genericsArray;
            $arr2 = $obj->genericsArray;
            for ($i = 0; $i < $genericsCount; $i++) {
                if ($arr1[$i]->Equals($arr2[$i]) == false)
                    return false;
            }
        }
        return true;
    }

    public function __toString()
    {
        return $this->FullName();
    }

}