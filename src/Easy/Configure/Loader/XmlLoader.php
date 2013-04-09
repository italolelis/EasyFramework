<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Configure\Loader;

use Symfony\Component\Config\Loader\FileLoader;

/**
 * Handles Yml config files
 */
class XmlLoader extends FileLoader
{

    public function load($resource, $type = null)
    {
        return null;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'xml' === pathinfo(
                        $resource, PATHINFO_EXTENSION
        );
    }

}
