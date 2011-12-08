<?php

/**
 *  Esse é o arquivo de entrada para todas as requisições do EasyFramework. A partir
 *  daqui todos os arquivos necessários são carregados e a mágica começa.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
/**
 *  Inclui o arquivo de inicialização de todos os arquivos necesários para o
 *  funcionamento de sua aplicação.
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/* Caminho do Root da aplicação */
if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(dirname(dirname(__FILE__)))) . DS);
}

/* Caminho da pasta App */
if (!defined('APP_FOLDER')) {
    define('APP_FOLDER', dirname(dirname(dirname(__FILE__)) . DS));
}

/* Caminho da pasta App */
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(dirname(__FILE__)) . DS);
}

/* Caminho da pasta Core */
if (!defined('FRAMEWORK_PATH')) {
    define('FRAMEWORK_PATH', ROOT . 'easyframework' . DS);
}

if (!defined('CORE')) {
    define('CORE', FRAMEWORK_PATH . 'core' . DS);
}

/* Caminho da pasta Lib */
if (!defined('LIB')) {
    define('LIB', CORE . 'lib' . DS);
}

/* Caminho da View */
if (!defined('VIEW_PATH')) {
    define("VIEW_PATH", APP_PATH . 'view' . DS);
}

/* Caminho dos Includes */
if (!defined('LAYOUT_PATH')) {
    define("LAYOUT_PATH", APP_PATH . 'layouts' . DS);
}

if (require_once CORE . 'bootstrap.php') {
    Dispatcher::dispatch();
}
?>