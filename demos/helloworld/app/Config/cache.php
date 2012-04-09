<?php

/**
 * This is cache configuration file.
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
 * Turn off all caching application-wide.
 */
// Configure::write('Cache.disable', true);

/**
 * Pick the caching engine to use.  If APC is enabled use it.
 * If running via cli - apc is disabled by default. ensure it's available and enabled in this case
 *
 * Note: 'default' and other application caches should be configured in app/Config/bootstrap.php.
 *       Please check the comments in boostrap.php for more info on the cache engines available 
 *       and their setttings.
 */
$engine = 'File';
// In development mode, caches should expire quickly.
$duration = 3600;
if (Config::read('App.debug')) {
    $duration = '+10 seconds';
}

// Prefix each application on the same server with a different string, to avoid Memcache and APC conflicts.
$prefix = 'myapp_';

// Setup a 'default' cache configuration for use in the application.
Cache::config('default', array(
    'engine' => $engine, // [required]
    'duration' => $duration, // [optional]
    'probability' => 100, // [optional]
    'path' => CACHE, // [optional] use system tmp directory - remember to use absolute path
    'prefix' => $prefix . 'easy_', // [optional] prefix every cache file with this string
    'lock' => false, // [optional] use file locking
    'serialize' => true
));

/**
 * Configure the cache for model and datasource caches.
 * This cache configuration
 * is used to store schema descriptions, and table listings in connections.
 */
Cache::config('_easy_model_', array(
    'engine' => $engine,
    'prefix' => $prefix . 'easy_model_',
    'path' => CACHE . 'models' . DS,
    'serialize' => ($engine === 'File'),
    'duration' => $duration
));

/**
 * Configure the cache used for general framework caching.  Path information,
 * object listings, and translation cache files are stored with this configuration.
 */
Cache::config('_easy_core_', array(
    'engine' => $engine,
    'prefix' => $prefix . 'easy_core_',
    'path' => CACHE . 'persistent' . DS,
    'serialize' => ($engine === 'File'),
    'duration' => $duration
));
