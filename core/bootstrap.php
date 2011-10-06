<?php

/**
 *  Carregamento das funcionalidades básicas do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
//FIXME: Isso não devería estar aqui
session_start();

define('DS', DIRECTORY_SEPARATOR);

$server = dirname(dirname(__FILE__));

/* Caminho do Root da aplicação */
define("ROOT", $server . DS);
/* Caminho da pasta App */
define('APP_PATH', ROOT . 'app' . DS);
/* Caminho da pasta Care */
define('CORE', ROOT . 'core' . DS);
/* Caminho da View */
define("VIEW_PATH", APP_PATH . 'view' . DS);
/* Caminho dos Includes */
define("INCLUDE_PATH", VIEW_PATH . 'includes' . DS);

/* Inclui as classes baicas */
require_once 'basics.php';
/* Inclui os helpers */
App::import('Helper', 'html_helper');
/* Inclui as classes de funcionamento */
App::import('Core', array('controller', 'connection', 'dispatcher', 'datasource', 'inflector', 'model', 'mapper', 'request', 'security', 'session', 'view'));
/* Inclui a biblioteca smarty */
App::import('Lib', 'smarty/Smarty.class');
/* Inclui as configurações da app */
App::import('Config', array('functions', 'database', 'settings'));
?>