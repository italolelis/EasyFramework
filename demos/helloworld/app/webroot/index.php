<?php

/**
 * Index
 *
 * The Front Controller for handling every request
 * 
 * PHP 5
 *
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 * @package app
 * @since EasyFramework v 0.3
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * The full path to the directory which holds "app", WITHOUT a trailing DS.
 */
defined('ROOT') || define('ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))));

/**
 * The actual directory name for the "app".
 */
defined('APP_PATH') || define('APP_PATH', dirname(dirname(__FILE__)) . DS);

/**
 * The Framework folder path
 */
defined('FRAMEWORK_PATH') || define('FRAMEWORK_PATH', ROOT . DS . 'framework' . DS);

/**
 * The absolute path to the "easy" directory.
 *
 * Un-comment this line to specify a fixed path to EasyFW.
 * This should point at the directory containing `Easy`.
 *
 * For ease of development EasyFW uses PHP's include_path.  If you
 * cannot modify your include_path set this value.
 *
 * Leaving this constant undefined will result in it being defined in Cake/bootstrap.php
 */
//defined('EASY_CORE_INCLUDE_PATH') || define('EASY_CORE_INCLUDE_PATH', FRAMEWORK_PATH . 'Lib' . DS . 'Easy' . DS);

/**
 * Editing below this line should NOT be necessary.
 * Change at your own risk.
 *
 */
if (!defined('EASY_CORE_INCLUDE_PATH')) {
    if (function_exists('ini_set')) {
        ini_set('include_path', FRAMEWORK_PATH . 'Lib' . PATH_SEPARATOR . ini_get('include_path'));
    }
    if (!include('Easy' . DS . 'bootstrap.php')) {
        $failed = true;
    }
} else {
    if (!include(EASY_CORE_INCLUDE_PATH . 'bootstrap.php')) {
        $failed = true;
    }
}

if (!empty($failed)) {
    trigger_error("EasyFW core could not be found.  Check the value of EASY_CORE_INCLUDE_PATH in APP/webroot/index.php.  It should point to the directory containing your " . DS . "easy core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
}

App::uses('Dispatcher', 'Core/Dispatcher');

$dispatcher = new Dispatcher ();
$dispatcher->dispatch(new Request(Mapper::here()), new Response(array('charset' => Config::read('App.encoding'))));
