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
 * @package App
 * @since EasyFramework v 0.3
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Use the DS to separate the directories in other defines
 */
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

/**
 * The actual directory name for the "app".
 */
defined('APP_PATH') || define('APP_PATH', dirname(dirname(__FILE__)) . DS);

$easy = '../../../../../easyframework/framework/Easy/bootstrap.php';
require_once($easy);

use Easy\Network\Request,
    Easy\Network\Response,
    Easy\Routing\Dispatcher,
    Easy\Core\Config;

$dispatcher = new Dispatcher();
$dispatcher->dispatch(new Request(), new Response(array('charset' => Config::read('App.encoding'))));