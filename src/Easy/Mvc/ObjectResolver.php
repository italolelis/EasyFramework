<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc;

use Easy\Core\Object;
use ReflectionClass;
use ReflectionProperty;

/**
 * Object Resolver helps you to work with convertions and unaccesible properties in you object
 * 
 * @since 2.0.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class ObjectResolver extends Object
{

    /**
     * @var object 
     */
    private $object;

    /**
     * @var ReflectionClass 
     */
    private $reflactionModel;

    /**
     * Initializes a new instance of the ObjectResolver class.
     * @param object $object The object to be resolved
     */
    public function __construct($object)
    {
        $this->object = $object;
        $this->reflactionModel = new ReflectionClass($object);
    }

    /**
     * Converts the current model to an array
     * @param bool $usePrivate Whether to read private and protected values or not
     * @return array
     */
    public function toArray($usePrivate = true)
    {
        $data = (array) $this->object;

        $properties = $this->reflactionModel->getProperties();
        if (!empty($properties)) {
            foreach ($properties as $p) {
                if ($usePrivate) {
                    $this->turnAcessible($p);
                }

                $data[$p->getName()] = $p->getValue($this->object);
            }
        }
        return $data;
    }

    /**
     * Sets values to the current model
     * @param array $values The values to set to current model
     * @param bool $create Creates the property if doesn't exists
     * @param bool $setPrivateValues Set values to private and protected values
     */
    public function setValues(array $values, $create = true, $setPrivateValues = true)
    {
        foreach ($values as $key => $value) {
            if ($this->reflactionModel->hasProperty($key)) {
                $property = $this->reflactionModel->getProperty($key);
                if ($setPrivateValues) {
                    $this->turnAcessible($property);
                }
                $property->setValue($this->object, $value);
            } else {
                if ($create) {
                    $this->object->{$key} = $value;
                }
            }
        }
    }

    /**
     * Turn any non public property into an accessible property
     * @param ReflectionProperty $property
     */
    private function turnAcessible($property)
    {
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }
    }

}

