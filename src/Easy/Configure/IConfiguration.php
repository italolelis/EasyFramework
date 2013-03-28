<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
