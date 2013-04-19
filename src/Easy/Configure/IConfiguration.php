<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Configure;

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * An interface for configurations handler classes
 */
interface IConfiguration
{

    /**
     * Load the configuration files
     * @param LoaderInterface $loader
     */
    public function loadConfigFiles(LoaderInterface $loader, $type = null);
}
