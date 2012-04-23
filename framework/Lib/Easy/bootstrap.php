<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/* Path to the temporary files directory. */
if (!defined('TMP')) {
    define('TMP', APP_PATH . 'tmp' . DS);
}
/* Path to the cache files directory. It can be shared between hosts in a multi-server setup. */
if (!defined('CACHE')) {
    define('CACHE', TMP . 'cache' . DS);
}
/* Path to the log files directory. It can be shared between hosts in a multi-server setup. */
if (!defined('LOGS')) {
    define('LOGS', TMP . 'logs' . DS);
}

if (!defined('EASY_CORE_INCLUDE_PATH')) {
    define('EASY_CORE_INCLUDE_PATH', dirname(__FILE__));
}

if (!defined('CORE')) {
    define('CORE', EASY_CORE_INCLUDE_PATH . DS);
}

/* Basic classes */
require CORE . 'basics.php';
require CORE . 'Core' . DS . 'App.php';
require CORE . 'Error' . DS . 'Exceptions.php';

/* Register the autoload function for the Lazy load */
spl_autoload_register(array('App', 'load'), true);

/* Build the App configs */
App::build();
App::init();

App::uses('Object', 'Core');
App::uses('Config', 'Core');

App::uses('Mapper', 'Routing');
App::uses('I18n', 'Localization');

App::uses('Error', 'Error');
App::uses('Cache', 'Cache');
App::uses('Debugger', 'Utility');

App::uses('Security', 'Security');

Config::bootstrap();