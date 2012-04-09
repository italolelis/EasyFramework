<?php

/**
 * This is core configuration file.
 *
 * Use it to configure core behavior of EasyFW.
 *
 * PHP 5
 *
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easy.lellysinformatica.com)
 * @package       app
 * @since         EasyFramework v 0.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Session configuration.
 *
 * Contains an array of settings to use for session configuration. The default key is
 * used to define a default preset to use for sessions, any settings declared
 * here will override the settings of the default config.
 *
 * Options:
 *
 * - Session.cookie - The name of the cookie to use. Defaults to 'EASY'
 * - Session.timeout - The number of minutes you want sessions to live for. This timeout is handled by EasyFramework
 * - Session.cookieTimeout - The number of minutes you want session cookies to live for.
 * - Session.checkAgent - Do you want the user agent to be checked when starting sessions?
 * - Session.defaults - The default configuration set to use as a basis for your session.
 * - Session.handler - Can be used to enable a custom session handler. 
 * - Session.autoRegenerate - Enabling this setting, turns on automatic renewal of sessions, and sessionids that change frequently.
 * - Session.ini - An associative array of additional ini values to set.
 *
 * The built in defaults are:
 *
 * - 'php' - Uses settings defined in your php.ini.
 * - 'easy' - Saves session files in EASY's /tmp directory.
 * - 'database' - Uses CakePHP's database sessions.
 * - 'cache' - Use the Cache class to save sessions.
 *
 */
$config['Session'] = array(
    'defaults' => 'php',
    'cookie' => 'sc',
    'ini' => array(
        'session.cookie_httponly' => true
    )
);

$config['Security'] = array(
    'level' => 'medium',
    'salt' => 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi',
    'cipherSeed' => '76859309657453542496749683645',
    'hash' => 'md5'
);

/**
 * Set the App options
 * 
 * Options:
 *
 * - encoding - string - The encode that the application will use.
 * - language - string - The app language. This will be used for internationalization.
 * - timezone - string - The app timezone. This will be used for internationalization.
 * 
 */
$config['App'] = array(
    'encoding' => 'utf-8',
    'language' => 'pt_BR',
    'timezone' => 'America/Sao_Paulo',
    'debug' => true,
    'environment' => 'development'
);

/**
 * Turn off all loging application-wide.
 */
// $config['Log'] = array('enable => false');

/**
 * Configure the Error handler used to handle errors for your application.
 * By default Error::handleError() is used. It will display errors using Debugger, when
 * debug > 0 and log errors with Log when debug = 0.
 *
 * Options:
 *
 * - handler - callback - The callback to handle errors. You can set this to any callback type, including anonymous functions.
 * - level - int - The level of errors you are interested in capturing.
 * - trace - boolean - Include stack traces for errors in log files.
 *
 * @see ErrorHandler for more information on error handling and configuration.
 */
$config['Error'] = array(
    'handler' => 'Error::handleError',
    'level' => E_ALL,
    'trace' => true
);

/**
 * Configure the Exception handler used for uncaught exceptions.
 * By default, Error::handleException() is used. It will display a HTML page for the exception, and
 * while debug > 0, framework errors like Missing Controller will be displayed.
 * When debug = 0, framework errors will be coerced into generic HTTP errors.
 *
 * Options:
 *
 * - handler - callback - The callback to handle exceptions. You can set this to any callback type, including anonymous functions.
 * - renderer - string - The class responsible for rendering uncaught exceptions.
 * - log - boolean - Should Exceptions be logged?
 *
 * @see ErrorHandler for more information on exception handling and configuration.
 */
$config['Exception'] = array(
    'handler' => 'Error::handleException',
    'renderer' => 'ExceptionRender',
    'customErrors' => false,
    'log' => true
);

/**
 * View is where you set which template engine EasyFramework will use.
 * The default Template Engine is Smarty, you can easily integrate any type of
 * Template Engine, or simply crates your own Engine.
 *
 * Options:
 * 
 * - engine - string - The Template Engine name.
 * - options - array - The Template Engine options.
 * -- compile_dir - string - The path where the engine will put the compile files
 * -- cache_dir - string - The path where the engine will put the cached files
 * -- template_dir - array - The array of directories wherer the engine will manage
 * --- views - string - The path to the views directory
 * --- layouts - string - The path to the layouts directory
 * --- elements - string - The path to the elements directory
 */
$config['View'] = array(
    'engine' => 'smarty',
    'options' => array(
        'template_dir' => array(
            'views' => App::path("View"),
            'layouts' => App::path("Layout"),
            'elements' => App::path("Element")
        ),
        'compile_dir' => APP_PATH . "tmp/views" . DS,
        'cache_dir' => APP_PATH . "tmp/cache/views" . DS
    )
);
