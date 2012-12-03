<?php

/**
 * Basic EasyFw functionality.
 *
 * Core functions for including other source files, loading models and so forth.
 *
 * PHP 5
 *
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 0.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
use Easy\Core\App;
use Easy\Core\Config;
use Easy\Localization\I18n;
use Symfony\Component\Locale\Locale;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Basic defines for timing functions.
 */
define('SECOND', 1);
define('MINUTE', 60);
define('HOUR', 3600);
define('DAY', 86400);
define('WEEK', 604800);
define('MONTH', 2592000);
define('YEAR', 31536000);

/**
 * FROM CAKEPHP
 * 
 * Gets an environment variable from available sources, and provides emulation
 * for unsupported or inconsistent environment variables (i.e. DOCUMENT_ROOT on
 * IIS, or SCRIPT_NAME in CGI mode).  Also exposes some additional custom
 * environment information.
 *
 * @param  string $key Environment variable name.
 * @return string Environment variable setting.
 * @link http://book.cakephp.org/2.0/en/core-libraries/global-constants-and-functions.html#env
 */
function env($key)
{
    if ($key === 'HTTPS') {
        if (isset($_SERVER['HTTPS'])) {
            return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        }
        return (strpos(env('SCRIPT_URI'), 'https://') === 0);
    }

    if ($key === 'SCRIPT_NAME') {
        if (env('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
            $key = 'SCRIPT_URL';
        }
    }

    $val = null;
    if (isset($_SERVER[$key])) {
        $val = $_SERVER[$key];
    } elseif (isset($_ENV[$key])) {
        $val = $_ENV[$key];
    } elseif (getenv($key) !== false) {
        $val = getenv($key);
    }

    if ($key === 'REMOTE_ADDR' && $val === env('SERVER_ADDR')) {
        $addr = env('HTTP_PC_REMOTE_ADDR');
        if ($addr !== null) {
            $val = $addr;
        }
    }

    if ($val !== null) {
        return $val;
    }

    switch ($key) {
        case 'SCRIPT_FILENAME':
            if (defined('SERVER_IIS') && SERVER_IIS === true) {
                return str_replace('\\\\', '\\', env('PATH_TRANSLATED'));
            }
            break;
        case 'DOCUMENT_ROOT':
            $name = env('SCRIPT_NAME');
            $filename = env('SCRIPT_FILENAME');
            $offset = 0;
            if (!strpos($name, '.php')) {
                $offset = 4;
            }
            return substr($filename, 0, strlen($filename) - (strlen($name) + $offset));
            break;
        case 'PHP_SELF':
            return str_replace(env('DOCUMENT_ROOT'), '', env('SCRIPT_FILENAME'));
            break;
        case 'CGI_MODE':
            return (PHP_SAPI === 'cgi');
            break;
        case 'HTTP_BASE':
            $host = env('HTTP_HOST');
            $parts = explode('.', $host);
            $count = count($parts);

            if ($count === 1) {
                return '.' . $host;
            } elseif ($count === 2) {
                return '.' . $host;
            } elseif ($count === 3) {
                $gTLD = array(
                    'aero',
                    'asia',
                    'biz',
                    'cat',
                    'com',
                    'coop',
                    'edu',
                    'gov',
                    'info',
                    'int',
                    'jobs',
                    'mil',
                    'mobi',
                    'museum',
                    'name',
                    'net',
                    'org',
                    'pro',
                    'tel',
                    'travel',
                    'xxx'
                );
                if (in_array($parts[1], $gTLD)) {
                    return '.' . $host;
                }
            }
            array_shift($parts);
            return '.' . implode('.', $parts);
            break;
    }
    return null;
}

/**
 * FROM CAKEPHP
 * 
 * Recursively strips slashes from all values in an array
 *
 * @param array $values Array of values to strip slashes
 * @return mixed What is returned from calling stripslashes
 * @link http://book.cakephp.org/2.0/en/core-libraries/global-constants-and-functions.html#stripslashes_deep
 */
function stripslashes_deep($values)
{
    if (is_array($values)) {
        foreach ($values as $key => $value) {
            $values[$key] = stripslashes_deep($value);
        }
    } else {
        $values = stripslashes($values);
    }
    return $values;
}

/**
 * FROM CAKEPHP
 * 
 * Print_r convenience function, which prints out <PRE> tags around
 * the output of given array. Similar to debug().
 *
 * @see	debug()
 * @param array $var Variable to print out
 * @link http://book.cakephp.org/2.0/en/core-libraries/global-constants-and-functions.html#pr
 */
function pr($var)
{
    if (App::isDebug() > 0) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}

/**
 * Returns a translated string if one is found; Otherwise, the submitted message.
 *
 * @param string $value Text to translate
 * @param mixed $args Array with arguments or multiple arguments in function
 * @return mixed translated string
 */
function __($value, $args = null)
{
    if (!$value) {
        return;
    }
    $translated = I18n::translate($value);
//    $translator = Config::read('translator');
//    if (!$translator) {
//        $translator = configTranslator();
//        Config::write("translator", $translator);
//    }
//    \Easy\Utility\Debugger::dump($translator->trans($value));
//    $translated = $translator->trans($value);
    if ($args === null) {
        return $translated;
    } elseif (!is_array($args)) {
        $args = array_slice(func_get_args(), 1);
    }
    return vsprintf($translated, $args);
}

function configTranslator()
{
    $locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $translator = new Translator($locale);
    $translator->setFallbackLocale(Config::read('Components.Translator.default_locale'));
    $translator->addLoader('pofile', new PoFileLoader());
    $iterator = new FilesystemIterator(APP_PATH . "Locale/LC_MESSAGES");
    $filter = new RegexIterator($iterator, '/\.(po)$/');
    foreach ($filter as $entry) {
        $translator->addResource('pofile', $entry->getPathname(), $translator->getLocale());
    }

    return $translator;
}

/**
 * Allows you to override the current domain for a single message lookup.
 *
 * @param string $domain Domain
 * @param string $msg String to translate
 * @param mixed $args Array with arguments or multiple arguments in function
 * @return translated string
 */
function __d($domain, $msg, $args = null)
{
    if (!$msg) {
        return;
    }
    $translated = I18n::translate($msg, null, $domain);
    if ($args === null) {
        return $translated;
    } elseif (!is_array($args)) {
        $args = array_slice(func_get_args(), 2);
    }
    return vsprintf($translated, $args);
}

/**
 * Split the namespace from the classname.
 *
 * Commonly used like `list($namespace, $classname) = namespaceSplit($class);`
 *
 * @param string $class The full class name, ie `Cake\Core\App`
 * @return array Array with 2 indexes. 0 => namespace, 1 => classname
 */
function namespaceSplit($class)
{
    $pos = strrpos($class, '\\');
    if ($pos === false) {
        return array('', $class);
    }
    return array(substr($class, 0, $pos), substr($class, $pos + 1));
}

/**
 * Used to delete files in the cache directories, or clear contents of cache directories
 *
 * @param string|array $params As String name to be searched for deletion, if name is a directory all files in
 *   directory will be deleted. If array, names to be searched for deletion. If clearCache() without params,
 *   all files in app/tmp/cache/views will be deleted
 * @param string $type Directory in tmp/cache defaults to view directory
 * @param string $ext The file extension you are deleting
 * @return true if files found and deleted false otherwise
 */
function clearCache($params = null, $type = 'views', $ext = '.php')
{
    if (is_string($params) || $params === null) {
        $params = preg_replace('/\/\//', '/', $params);
        $cache = CACHE . $type . DS . $params;

        if (is_file($cache . $ext)) {
            @unlink($cache . $ext);
            return true;
        } elseif (is_dir($cache)) {
            $files = glob($cache . '*');

            if ($files === false) {
                return false;
            }

            foreach ($files as $file) {
                if (is_file($file) && strrpos($file, DS . 'empty') !== strlen($file) - 6) {
                    @unlink($file);
                }
            }
            return true;
        } else {
            $cache = array(
                CACHE . $type . DS . '*' . $params . $ext,
                CACHE . $type . DS . '*' . $params . '_*' . $ext
            );
            $files = array();
            while ($search = array_shift($cache)) {
                $results = glob($search);
                if ($results !== false) {
                    $files = array_merge($files, $results);
                }
            }
            if (empty($files)) {
                return false;
            }
            foreach ($files as $file) {
                if (is_file($file) && strrpos($file, DS . 'empty') !== strlen($file) - 6) {
                    @unlink($file);
                }
            }
            return true;
        }
    } elseif (is_array($params)) {
        foreach ($params as $file) {
            clearCache($file, $type, $ext);
        }
        return true;
    }
    return false;
}

if (!function_exists('h')) {

    /**
     * Convenience method for htmlspecialchars.
     *
     * @param string|array|object $text Text to wrap through htmlspecialchars.  Also works with arrays, and objects.
     *    Arrays will be mapped and have all their elements escaped.  Objects will be string cast if they
     *    implement a `__toString` method.  Otherwise the class name will be used.
     * @param boolean $double Encode existing html entities
     * @param string $charset Character set to use when escaping.  Defaults to config value in 'App.encoding' or 'UTF-8'
     * @return string Wrapped text
     * @link http://book.cakephp.org/2.0/en/core-libraries/global-constants-and-functions.html#h
     */
    function h($text, $double = true, $charset = null)
    {
        if (is_array($text)) {
            $texts = array();
            foreach ($text as $k => $t) {
                $texts[$k] = h($t, $double, $charset);
            }
            return $texts;
        } elseif (is_object($text)) {
            if (method_exists($text, '__toString')) {
                $text = (string) $text;
            } else {
                $text = '(object)' . get_class($text);
            }
        } elseif (is_bool($text)) {
            return $text;
        }

        static $defaultCharset = false;
        if ($defaultCharset === false) {
            $defaultCharset = Config::read('App.encoding');
            if ($defaultCharset === null) {
                $defaultCharset = 'UTF-8';
            }
        }
        if (is_string($double)) {
            $charset = $double;
        }
        return htmlspecialchars($text, ENT_QUOTES, ($charset) ? $charset : $defaultCharset, $double);
    }

}
