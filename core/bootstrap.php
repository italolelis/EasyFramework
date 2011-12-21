<?php

/**
 *  Carregamento das funcionalidades básicas do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
/**
 * Path to the temporary files directory.
 */
if (!defined('TMP')) {
    define('TMP', APP_PATH . 'tmp' . DS);
}

/**
 * Path to the cache files directory. It can be shared between hosts in a multi-server setup.
 */
define('CACHE', TMP . 'cache' . DS);
/**
 * Path to the log files directory. It can be shared between hosts in a multi-server setup.
 */
define('LOGS', TMP . 'logs' . DS);

/* Basic classes */
require CORE . 'Common' . DS . 'App.php';
require CORE . 'Debug' . DS . 'Exceptions.php';

spl_autoload_register(array('App', 'load'));
App::build();

App::uses('Cache', 'Core/Cache');
App::uses('Debug', 'Core/Debug');
App::uses('Config', 'Core/Common');
App::uses('Enum', 'Core/Common');

App::uses('Inflector', 'Core/Common');
App::uses('ClassRegistry', 'Core/Utility');
App::uses('FileSystem', 'Core/Utility');

App::uses('Dispatcher', 'Core/Dispatcher');
App::uses('Mapper', 'Core/Dispatcher');

App::uses('Controller', 'Core/Controller');
App::uses('Model', 'Core/Model');
App::uses('View', 'Core/View');

App::uses('Security', 'Core/Security');

App::import('Config', array('database', 'settings'));

App::uses('AppController', 'App/controllers');
App::uses('AppModel', 'App/models');

Debug::handleExceptions();
Debug::handleErrors();

///* Core classes */
//App::import("Core", array(
//    "Annotations/AnnotationManager",
//    //Cache System
//    "Cache/Cache",
//    //Common Files
//    "Common/Config",
//    "Common/Enum",
//    "Common/Hookable",
//    "Common/Validation",
//    "Common/Inflector",
//    //Ultility classes
//    "Utility/ClassRegistry",
//    "Utility/FileSystem",
//    //Controller Manager
//    "Controller/Controller",
//    //Debug System
//    "Debug/Debug",
//    //Dispatcher System
//    "Dispatcher/Dispatcher",
//    "Dispatcher/Mapper",
//    //Model Manager
//    "Model/Model",
//    //Security System
//    "Security/Security",
//    //Storage System
//    "Storage/Cookie",
//    "Storage/Session",
//    //View Manager
//    "View/View"
//));
///* Import the Smarty's lib */
//App::import("Lib", "smarty/Smarty.class");
///* Import the app configs */
//App::import('Config', array('functions', 'database', 'settings'));
///*  Import the app base classes */
//App::import("App", "controllers/app_controller");
//App::import("App", "models/app_model");
?>