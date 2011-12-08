<?php

/**
 *  Carregamento das funcionalidades básicas do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
/* Basic classes */
require_once "common/App.php";

/* Core classes */
App::import("Core", array(
    "annotations/AnnotationManager",
    //Common Files
    "common/Config",
    "common/enum",
    "common/ClassRegistry",
    "common/filesystem",
    "common/hookable",
    "common/validation",
    "common/inflector",
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