<?php

/**
 *  Carregamento das funcionalidades básicas do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
/* Basic classes */
require_once "common/basics.php";

/* Core classes */
App::import("Core", array(
    "annotations/annotation",
    //Common Files
    "common/CJSON",
    "common/enum",
    "common/class_registry",
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
/*  Inclusão das classes da biblioteca do EasyFramework ou das classes as sobrescrevem */
App::import("Controller", "app_controller");
App::import("Model", "app_model");
?>