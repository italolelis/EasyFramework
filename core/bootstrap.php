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
    define('TMP', APP_PATH . 'webroot/tmp' . DS);
}

/**
 * Path to the cache files directory. It can be shared between hosts in a multi-server setup.
 */
define('CACHE', TMP . 'cache' . DS);

/* Basic classes */
require_once "common/App.php";

App::build();
//spl_autoload_register(array('App', 'load'));
//
//App::uses('AnnotationManager', 'Annotation');
//App::uses('Cache', 'Cache');
//App::uses('Config', 'Ccommon');
//App::uses('enum', 'Common');
//App::uses('Hookable', 'Common');
//App::uses('validation', 'Common');
//App::uses('Inflector', 'Common');
//App::uses('ClassRegistry', 'Utility');
//App::uses('Utility/FileSystem', 'Core');
//App::uses('Controller', 'Controller');
//App::uses('Debug', 'Core/debug');
//
//App::uses('Dispatcher', 'Dispatcher');
//
//App::uses('Mapper', 'Dispatcher');
//App::uses('model', 'Model');
//App::uses('security/security', 'Core');
//App::uses('storage/cookie', 'Core');
//App::uses('storage/session', 'Core');
//App::uses('view/view', 'Core');
//
//App::uses('smarty/Smarty.class', 'Lib');
//
//App::uses('functions', 'Config');
//App::uses('database', 'Config');
//App::uses('settings', 'Config');
//
//App::uses('app_controller', 'Controller');
//App::uses('app_model', 'Model');

/* Core classes */
App::import("Core", array(
    "annotations/AnnotationManager",
    //Cache System
    "Cache/Cache",
    //Common Files
    "common/Config",
    "common/enum",
    "common/hookable",
    "common/validation",
    "common/inflector",
    //Ultility classes
    "Utility/ClassRegistry",
    "Utility/FileSystem",
    //Controller Manager
    "controller/controller",
    //Debug System
    "debug/debug",
    //Dispatcher System
    "dispatcher/dispatcher",
    "dispatcher/mapper",
    //Model Manager
    "model/model",
    //Security System
    "security/security",
    //Storage System
    "storage/cookie",
    "storage/session",
    //View Manager
    "view/view"
));
/* Import the Smarty's lib */
App::import("Lib", "smarty/Smarty.class");
/* Import the app configs */
App::import('Config', array('functions', 'database', 'settings'));
/*  Import the app base classes */
App::import("Controller", "app_controller");
App::import("Model", "app_model");
?>