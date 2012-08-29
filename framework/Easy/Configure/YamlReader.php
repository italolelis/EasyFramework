<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 1.5.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Configure;

use Easy\Core\App;

App::import('Vendors', 'Yaml/Yaml');

/**
 * Handles Yml config files
 * 
 * @package       Easy.Configure
 * @see http://php.net/parse_ini_file
 */
class YamlReader implements ConfigReaderInterface
{

    /**
     * The path to read ini files from.
     *
     * @var array
     */
    protected $_path;

    /**
     * Build and construct a new ini file parser. The parser can be used to read
     * ini files that are on the filesystem.
     *
     * @param string $path Path to load ini config files from.
     * @param string $section Only get one section, leave null to parse and fetch
     *     all sections in the ini file.
     */
    public function __construct($path, $section = null)
    {
        $this->_path = $path;
    }

    /**
     * Read an ini file and return the results as an array.
     *
     * @param string $file Name of the file to read. The chosen file
     *    must be on the reader's path.
     * @return array
     * @throws ConfigureException
     */
    public function read($file)
    {
        return \Yaml::parse($this->_path . $file . '.yml');
    }

}
