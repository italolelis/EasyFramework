<?php

/**
 *  Esse é o arquivo das principais configurações do EasyFramework. Através delas,
 *  você pode configurar o comportamento do núcleo do EasyFramework.
 */
/**
 * Essa rota define o controller padrão de sua aplicação, aquele que o usuário
 * verá toda vez que acessar a raíz de seu sistema. Você pode escolher o controller
 * que mais fizer sentido para você
 */
//Mapper::setRoot("home");

/**
 * Session configuration.
 *
 * Contains an array of settings to use for session configuration. The defaults key is
 * used to define a default preset to use for sessions, any settings declared here will override
 * the settings of the default config.
 *
 * ## Options
 *
 * - `Session.cookie` - The name of the cookie to use. Defaults to 'EASY'
 * - `Session.timeout` - The number of minutes you want sessions to live for. This timeout is handled by EasyFramework
 * - `Session.cookieTimeout` - The number of minutes you want session cookies to live for.
 * - `Session.checkAgent` - Do you want the user agent to be checked when starting sessions? You might want to set the
 *    value to false, when dealing with older versions of IE, Chrome Frame or certain web-browsing devices and AJAX
 * - `Session.defaults` - The default configuration set to use as a basis for your session.
 *    There are four builtins: php, cake, cache, database.
 * - `Session.handler` - Can be used to enable a custom session handler.  Expects an array of of callables,
 *    that can be used with `session_save_handler`.  Using this option will automatically add `session.save_handler`
 *    to the ini array.
 * - `Session.autoRegenerate` - Enabling this setting, turns on automatic renewal of sessions, and
 *    sessionids that change frequently. See CakeSession::$requestCountdown.
 * - `Session.ini` - An associative array of additional ini values to set.
 *
 * The built in defaults are:
 *
 * - 'php' - Uses settings defined in your php.ini.
 * - 'cake' - Saves session files in EASY's /tmp directory.
 * - 'database' - Uses CakePHP's database sessions.
 * - 'cache' - Use the Cache class to save sessions.
 *
 * To define a custom session handler, save it at /app/Storage/<name>.php.
 * Make sure the class implements `ISessionHandler` and set Session.handler to <name>
 *
 * To use database sessions, run the app/Config/Schema/sessions.php schema using
 * the cake shell command: cake schema create Sessions
 *
 */
Config::write('Session', array(
    'defaults' => 'php'
));

/**
 * A random string used in security hashing methods.
 */
Config::write('Security.salt', 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi');

/**
 * Turn off all caching application-wide.
 *
 */
//Configure::write('Cache.disable', true);

/**
 * Turn off all loging application-wide.
 *
 */
//Config::write('Log.enable', false);

/**
 * Configure the Error handler used to handle errors for your application.  By default
 * Error::handleError() is used.  It will display errors using Debugger, when debug > 0
 * and log errors with Log when debug = 0.
 *
 * Options:
 *
 * - `handler` - callback - The callback to handle errors. You can set this to any callback type,
 *    including anonymous functions.
 * - `level` - int - The level of errors you are interested in capturing.
 * - `trace` - boolean - Include stack traces for errors in log files.
 *
 * @see ErrorHandler for more information on error handling and configuration.
 */
Config::write('Error', array(
    'handler' => 'Error::handleError',
    'level' => E_ALL & ~E_DEPRECATED,
    'trace' => true
));
/**
 * Configure the Exception handler used for uncaught exceptions.  By default,
 * Error::handleException() is used. It will display a HTML page for the exception, and
 * while debug > 0, framework errors like Missing Controller will be displayed.  When debug = 0,
 * framework errors will be coerced into generic HTTP errors.
 *
 * Options:
 *
 * - `handler` - callback - The callback to handle exceptions. You can set this to any callback type,
 *   including anonymous functions.
 * - `renderer` - string - The class responsible for rendering uncaught exceptions.  If you choose a custom class you
 *   should place the file for that class in app/Lib/Error. This class needs to implement a render method.
 * - `log` - boolean - Should Exceptions be logged?
 *
 * @see ErrorHandler for more information on exception handling and configuration.
 */
Config::write('Exception', array(
    'handler' => 'Error::handleException',
    'renderer' => 'ExceptionRender',
    'log' => true
));
/**
 *  Template é onde você poderá configurar o comportamento do seu template(views).
 *  Nele podem ser setadas configurações como: Cacheble: se um template será guardado
 *  em cache, Urls: Define as urls que serão passadas para a view.
 */
Config::write("template.layouts", array(
    "layout" => "layout.tpl"
));

Config::write("template.cache", array(
    "cache" => false, //Cache desabilitado
    "time" => 3600
));
//Configuramos as urls que serão usuadas nas views
Config::write("template.urls", array(
    'home' => 'home',
    'usuarios' => 'usuarios',
    'incluirUsuario' => 'usuarios/incluir',
    'editarUsuario' => 'usuarios/edit/',
    'excluirUsuario' => 'usuarios/excluir/',
));

/**
 *
 * Cache Engine Configuration
 * Default settings provided below
 *
 * File storage engine.
 *
 * 	 Cache::config('default', array(
 * 		'engine' => 'File', //[required]
 * 		'duration'=> 3600, //[optional]
 * 		'probability'=> 100, //[optional]
 * 		'path' => CACHE, //[optional] use system tmp directory - remember to use absolute path
 * 		'prefix' => 'easy_', //[optional]  prefix every cache file with this string
 * 		'lock' => false, //[optional]  use file locking
 * 		'serialize' => true, [optional]
 * 	));
 *
 * APC (http://pecl.php.net/package/APC)
 *
 * 	 Cache::config('default', array(
 * 		'engine' => 'Apc', //[required]
 * 		'duration'=> 3600, //[optional]
 * 		'probability'=> 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 * 	));
 *
 * Xcache (http://xcache.lighttpd.net/)
 *
 * 	 Cache::config('default', array(
 * 		'engine' => 'Xcache', //[required]
 * 		'duration'=> 3600, //[optional]
 * 		'probability'=> 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional] prefix every cache file with this string
 * 		'user' => 'user', //user from xcache.admin.user settings
 * 		'password' => 'password', //plaintext password (xcache.admin.pass)
 * 	));
 *
 * Memcache (http://www.danga.com/memcached/)
 *
 * 	 Cache::config('default', array(
 * 		'engine' => 'Memcache', //[required]
 * 		'duration'=> 3600, //[optional]
 * 		'probability'=> 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 * 		'servers' => array(
 * 			'127.0.0.1:11211' // localhost, default port 11211
 * 		), //[optional]
 * 		'persistent' => true, // [optional] set this to false for non-persistent connections
 * 		'compress' => false, // [optional] compress data in Memcache (slower, but uses less memory)
 * 	));
 *
 *  Wincache (http://php.net/wincache)
 *
 * 	 Cache::config('default', array(
 * 		'engine' => 'Wincache', //[required]
 * 		'duration'=> 3600, //[optional]
 * 		'probability'=> 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 * 	));
 */
$engine = 'File';
// In development mode, caches should expire quickly.
$duration = 3600;
if (Config::read('debug')) {
    $duration = '+10 seconds';
}

// Setup a 'default' cache configuration for use in the application.
Cache::config('default', array(
    'engine' => $engine, //[required]
    'duration' => $duration, //[optional]
    'probability' => 100, //[optional]
    'path' => CACHE, //[optional] use system tmp directory - remember to use absolute path
    'prefix' => 'easy_', //[optional]  prefix every cache file with this string
    'lock' => false, //[optional]  use file locking
    'serialize' => true,
));

/**
 * Configure the cache for model and datasource caches.  This cache configuration
 * is used to store schema descriptions, and table listings in connections.
 */
Cache::config('_easy_model_', array(
    'engine' => $engine,
    'prefix' => 'easy_model_',
    'path' => CACHE . 'models' . DS,
    'serialize' => ($engine === 'File'),
    'duration' => $duration
));
?>
