<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Configure\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Handles Yml config files
 */
class YamlLoader extends FileLoader
{

    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);
        //Yaml::enablePhpParsing();
        return Yaml::parse($path);
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
                        $resource, PATHINFO_EXTENSION
        );
    }

}
