<?php

/**
 *  Carregamento das funcionalidades básicas do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
/* Inclui as classes baicas */
require_once 'basics.php';
/* Inclui as classes de funcionamento */
App::import('Core', array("class_registry", "component", "connection", "controller", "cookie", 'dispatcher', 'datasource', 'inflector', 'model', 'mapper', 'security', 'session', 'view'));
/* Inclui a biblioteca smarty */
App::import('Lib', 'smarty/Smarty.class');
/* Inclui as configurações da app */
App::import('Config', array('functions', 'database', 'settings'));
/*  Inclusão das classes da biblioteca do EasyFramework ou das classes as sobrescrevem */
App::import("Controller", "app_controller");
App::import("Model", "app_model");
?>