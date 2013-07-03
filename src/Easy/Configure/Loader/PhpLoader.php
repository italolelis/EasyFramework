<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Configure\Loader;

use Symfony\Component\Config\Loader\FileLoader;

/**
 * Handles Yml config files
 */
class PhpLoader extends FileLoader
{

    public function load($resource, $type = null)
    {
        if (strpos($resource, '..') !== false) {
            throw new ConfigureException(__('Cannot load configuration files with ../ in them.'));
        }
        if (substr($resource, -4) === '.php') {
            $resource = substr($resource, 0, -4);
        }

        $file = $resource;
        $file .= '.php';

        if (!is_file($file)) {
            if (!is_file(substr($file, 0, -4))) {
                throw new ConfigureException(__('Could not load configuration files: %s or %s', $file, substr($file, 0, -4)));
            }
        }
        $config = file_get_contents($file);
        return $config;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo(
                        $resource, PATHINFO_EXTENSION
        );
    }

}
