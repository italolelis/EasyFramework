<?php

/**
 * FROM CAKEPHP
 * 
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 * @since         EasyFramework v 1.5.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * An interface for creating objects compatible with Configure::load()
 *
 * @package       Easy.Configure
 */
interface ConfigReaderInterface {

    /**
     * Read method is used for reading configuration information from sources.
     * These sources can either be static resources like files, or dynamic ones like
     * a database, or other datasource.
     *
     * @param string $key
     * @return array An array of data to merge into the runtime configuration
     */
    public function read($key);
}
