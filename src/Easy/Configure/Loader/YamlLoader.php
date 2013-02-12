<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
        Yaml::enablePhpParsing();
        return Yaml::parse($resource);
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
                        $resource, PATHINFO_EXTENSION
        );
    }

}
