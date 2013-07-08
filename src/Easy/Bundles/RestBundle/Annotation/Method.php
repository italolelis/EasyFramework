<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\RestBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Method
{

    public $value;

    public function getValue()
    {
        return $this->value;
    }

}