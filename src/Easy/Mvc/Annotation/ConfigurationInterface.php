<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Annotation;

abstract class ConfigurationAnnotation implements ConfigurationInterface
{

    public function __construct(array $values)
    {
        foreach ($values as $k => $v) {
            if (!method_exists($this, $name = 'set' . $k)) {
                throw new \RuntimeException(sprintf('Unknown key "%s" for annotation "@%s".', $k, get_class($this)));
            }

            $this->$name($v);
        }
    }

}