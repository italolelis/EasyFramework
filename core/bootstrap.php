<?php

/**
 *  Carregamento das funcionalidades básicas do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
/* Path to the temporary files directory. */
defined('TMP')
        || define('TMP', APP_PATH . 'tmp' . DS);
/* Path to the cache files directory. It can be shared between hosts in a multi-server setup. */
defined('CACHE')
        || define('CACHE', TMP . 'cache' . DS);
/* Path to the log files directory. It can be shared between hosts in a multi-server setup. */
defined('LOGS')
        || define('LOGS', TMP . 'logs' . DS);

/* Basic classes */
require CORE . 'basics.php';
require CORE . 'Common' . DS . 'App.php';
require CORE . 'Error' . DS . 'Exceptions.php';

/* Register the autoload function for the Lazy load */
spl_autoload_register(array('App', 'load'));

/* Build the App configs */
App::build();

App::uses('Cache', 'Core/Cache');
App::uses('Debug', 'Core/Debug');
App::uses('Config', 'Core/Common');
App::uses('Error', 'Core/Error');

App::uses('Inflector', 'Core/Common');
App::uses('ClassRegistry', 'Core/Utility');

App::uses('Dispatcher', 'Core/Dispatcher');
App::uses('Mapper', 'Core/Dispatcher');

App::uses('Controller', 'Core/Controller');
App::uses('Model', 'Core/Model');
App::uses('View', 'Core/View');

App::uses('Security', 'Core/Security');

App::uses('AppController', 'App/controllers');
App::uses('AppModel', 'App/models');

App::import('Config', array('database', 'settings', 'routes'));

/* Handle the Exceptions and Errors */
Error::handleExceptions();
Error::setErrorReporting(Config::read('Error.level'));
//Error::handleErrors();
?>