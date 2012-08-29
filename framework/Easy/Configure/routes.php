<?php

/**
 * FROM CAKEPHP
 * 
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @package       app
 * @since         EasyFramework v 1.6.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
use Easy\Routing\Mapper;

/**
 * Connects the default, built-in routes. The following routes are created in the order below:
 * 
 * - `/:controller'
 * - `/:controller/:action/*'
 *
 * You can disable the connection of default routes by deleting the require inside APP/Config/routes.yaml.
 */
$prefixes = Mapper::prefixes();

foreach ($prefixes as $prefix) {
    $params = array('prefix' => $prefix, $prefix => true);
    $indexParams = $params + array('action' => 'index');
    Mapper::connect("/{$prefix}/:controller", $indexParams);
    Mapper::connect("/{$prefix}/:controller/:action/*", $params);
}
Mapper::connect('/:controller', array('action' => 'index'));
Mapper::connect('/:controller/:action/*');

$namedConfig = Mapper::namedConfig();
if ($namedConfig['rules'] === false) {
    Mapper::connectNamed(true);
}

unset($namedConfig, $params, $indexParams, $prefix, $prefixes);
