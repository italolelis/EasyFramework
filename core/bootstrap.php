<?php

/**
 *  Carregamento das funcionalidades básicas do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
/* Basic classes */
require_once "Common/App.php";

/* Core classes */
App::import("Core", array(
    "Annotations/AnnotationFactory",
    //Common Files
    "Common/Config",
    "Common/Enum",
    "Common/ClassRegistry",
    "Common/FileSystem",
    "Common/Hookable",
    "Common/Validation",
    "Common/Inflector",
    //Controller Manager
    "Controller/Controller",
    //Debug System
    "Debug/Debug",
    //Dispatcher System
    "Dispatcher/Dispatcher",
    "Dispatcher/Mapper",
    //Model Manager
    "Model/Model",
    //Security System
    "Security/Security",
    //Storage System
    "Storage/Cookie",
    "Storage/Session",
    //View Manager
    "View/View"
));
/* Import the Smarty's lib */
App::import("Lib", "smarty/Smarty.class");
/* Import the app configs */
App::import('Config', array('functions', 'database', 'settings'));
/*  Inclusão das classes da biblioteca do EasyFramework ou das classes as sobrescrevem */
App::import("Controller", "app_controller");
App::import("Model", "app_model");
?>