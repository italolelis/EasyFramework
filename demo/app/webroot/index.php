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
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

/**
 * The Root server path
 */
defined('ROOT') || define('ROOT', dirname(dirname(dirname(dirname(__FILE__)))) . DS);

/**
 * Caminho da pasta App
 */
defined('APP_PATH') || define('APP_PATH', dirname(dirname(__FILE__)) . DS);

/**
 * The Framework folder path
 */
defined('FRAMEWORK_PATH') || define('FRAMEWORK_PATH', ROOT . 'easyframework' . DS);

/**
 * The Core folder Path
 */
defined('CORE') || define('CORE', FRAMEWORK_PATH . 'core' . DS);

if (require CORE . 'bootstrap.php') {
    $dispatcher = new Dispatcher ();
    $dispatcher->dispatch(new Request(Mapper::here()), new Response(array('charset' => Config::read('App.encoding'))));
}