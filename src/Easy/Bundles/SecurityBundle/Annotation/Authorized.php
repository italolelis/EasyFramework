<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SecurityBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Authorized
{

    public $roles;

    public function getRoles()
    {
        return $this->roles;
    }

}