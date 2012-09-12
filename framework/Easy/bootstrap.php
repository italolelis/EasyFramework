<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Use the DS to separate the directories in other defines
 */
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
/**
 * Defines the framework installation path.
 */
defined('CORE') || define('CORE', dirname(__FILE__) . DS);
/**
 * Path to the temporary files directory.
 */
defined('TMP') || define('TMP', APP_PATH . 'tmp' . DS);
/**
 * Path to the cache files directory. It can be shared between hosts in a multi-server setup.
 */
defined('CACHE') || define('CACHE', TMP . 'cache' . DS);
/**
 * Path to the log files directory. It can be shared between hosts in a multi-server setup.
 */
defined('LOGS') || define('LOGS', TMP . 'logs' . DS);

if (!defined('LIB_PATH')) {
    define('LIB_PATH', dirname(dirname(__FILE__)));
}


/* Basic classes */
require CORE . 'basics.php';
require CORE . DS . 'Core' . DS . 'ClassLoader.php';

/**
 *  Full url prefix
 */
if (!defined('FULL_BASE_URL')) {
    $s = null;
    if (env('HTTPS')) {
        $s = 's';
    }

    $httpHost = env('HTTP_HOST');

    if (isset($httpHost)) {
        define('FULL_BASE_URL', 'http' . $s . '://' . $httpHost . '/' . basename(dirname(APP_PATH)));
    }
    unset($httpHost, $s);
}

$loader = new \Easy\Core\ClassLoader('Easy', LIB_PATH);
$loader->register();

Easy\Core\Config::bootstrap();