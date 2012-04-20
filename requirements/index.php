<?php

/**
 * Basic EasyFw functionality.
 *
 * Core functions for including other source files, loading models and so forth.
 *
 * PHP 5
 * 
 * BASED ON YII FRAMEWORK
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
 * @var array List of requirements (name, required or not, result, used by, memo)
 */
$requirements = array(
    array(
        t('easy', 'PHP version'),
        true,
        version_compare(PHP_VERSION, "5.2.4", ">="),
        '<a href="http://www.easy.lellysinformatica.com">EasyFramework</a>',
        t('easy', 'PHP 5.2.4 or higher is required.')),
    array(
        t('easy', '$_SERVER variable'),
        true,
        ($message = checkServerVar()) === '',
        '<a href="http://www.easy.lellysinformatica.com">EasyFramework</a>',
        $message),
    array(
        t('easy', 'Reflection extension'),
        true,
        class_exists('Reflection', false),
        '<a href="http://www.easy.lellysinformatica.com">EasyFramework</a>',
        ''),
    array(
        t('easy', 'PCRE extension'),
        true,
        extension_loaded("pcre"),
        '<a href="http://www.easy.lellysinformatica.com">EasyFramework</a>',
        ''),
    array(
        t('easy', 'SPL extension'),
        true,
        extension_loaded("SPL"),
        '<a href="http://www.easy.lellysinformatica.com">EasyFramework</a>',
        ''),
    array(
        t('easy', 'DOM extension'),
        false,
        class_exists("DOMDocument", false),
        '<a href="http://www.yiiframework.com/doc/api/CHtmlPurifier">CHtmlPurifier</a>, <a href="http://www.yiiframework.com/doc/api/CWsdlGenerator">CWsdlGenerator</a>',
        ''),
    array(
        t('easy', 'PDO extension'),
        false,
        extension_loaded('pdo'),
        t('easy', 'All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
        ''),
    array(
        t('easy', 'PDO SQLite extension'),
        false,
        extension_loaded('pdo_sqlite'),
        t('easy', 'All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
        t('easy', 'This is required if you are using SQLite database.')),
    array(
        t('easy', 'PDO MySQL extension'),
        false,
        extension_loaded('pdo_mysql'),
        t('easy', 'All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
        t('easy', 'This is required if you are using MySQL database.')),
    array(
        t('easy', 'PDO PostgreSQL extension'),
        false,
        extension_loaded('pdo_pgsql'),
        t('easy', 'All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
        t('easy', 'This is required if you are using PostgreSQL database.')),
    array(
        t('easy', 'MySQLi extension'),
        false,
        extension_loaded('mysqli'),
        t('easy', 'All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
        t('easy', 'This is required if you are using MySQLi engine.')),
    array(
        t('easy', 'Memcache extension'),
        false,
        extension_loaded("memcache") || extension_loaded("memcached"),
        '<a href="http://www.yiiframework.com/doc/api/CMemCache">Cache Memcache</a>',
        extension_loaded("memcached") ? t('yii', 'To use memcached set <a href="http://www.yiiframework.com/doc/api/CMemCache#useMemcached-detail">CMemCache::useMemcached</a> to <code>true</code>.') : ''),
    array(
        t('easy', 'APC extension'),
        false,
        extension_loaded("apc"),
        '<a href="http://www.yiiframework.com/doc/api/CApcCache">Cache APC</a>',
        ''),
    array(
        t('easy', 'Mcrypt extension'),
        false,
        extension_loaded("mcrypt"),
        '<a href="http://www.easy.lellysinformatica.com/docs/1.x/security">Security Class</a>',
        t('easy', 'This is required by encrypt and decrypt methods.')),
    array(
        t('easy', 'SOAP extension'),
        false,
        extension_loaded("soap"),
        '<a href="http://www.yiiframework.com/doc/api/CWebService">CWebService</a>, <a href="http://www.yiiframework.com/doc/api/CWebServiceAction">CWebServiceAction</a>',
        ''),
    array(
        t('easy', 'GD extension with<br />FreeType support'),
        false,
        ($message = checkGD()) === '',
        //extension_loaded('gd'),
        '<a href="http://www.yiiframework.com/doc/api/CCaptchaAction">CCaptchaAction</a>',
        $message),
    array(
        t('easy', 'Ctype extension'),
        false,
        extension_loaded("ctype"),
        '<a href="http://www.yiiframework.com/doc/api/CDateFormatter">CDateFormatter</a>, <a href="http://www.yiiframework.com/doc/api/CDateFormatter">CDateTimeParser</a>, <a href="http://www.yiiframework.com/doc/api/CTextHighlighter">CTextHighlighter</a>, <a href="http://www.yiiframework.com/doc/api/CHtmlPurifier">CHtmlPurifier</a>',
        ''
    )
);

function checkServerVar() {
    $vars = array('HTTP_HOST', 'SERVER_NAME', 'SERVER_PORT', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'PHP_SELF', 'HTTP_ACCEPT', 'HTTP_USER_AGENT');
    $missing = array();
    foreach ($vars as $var) {
        if (!isset($_SERVER[$var]))
            $missing[] = $var;
    }
    if (!empty($missing))
        return t('easy', '$_SERVER does not have {vars}.', array('{vars}' => implode(', ', $missing)));

    if (realpath($_SERVER["SCRIPT_FILENAME"]) !== realpath(__FILE__))
        return t('easy', '$_SERVER["SCRIPT_FILENAME"] must be the same as the entry script file path.');

    if (!isset($_SERVER["REQUEST_URI"]) && isset($_SERVER["QUERY_STRING"]))
        return t('easy', 'Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.');

    if (!isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"], $_SERVER["SCRIPT_NAME"]) !== 0)
        return t('easy', 'Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.');

    return '';
}

function checkGD() {
    if (extension_loaded('gd')) {
        $gdinfo = gd_info();
        if ($gdinfo['FreeType Support'])
            return '';
        return t('easy', 'GD installed<br />FreeType support not installed');
    }
    return t('easy', 'GD not installed');
}

function getFrameworkVersion() {
    $coreFile = dirname(dirname(__FILE__)) . '/framework/Lib/Easy/version.ini';
    $ini = parse_ini_file($coreFile);
    return $ini['version'];
}

/**
 * Returns a localized message according to user preferred language.
 * @param string message category
 * @param string message to be translated
 * @param array parameters to be applied to the translated message
 * @return string translated message
 */
function t($category, $message, $params = array()) {
    static $messages;

    if ($messages === null) {
        $messages = array();
        if (($lang = getPreferredLanguage()) !== false) {
            $file = dirname(__FILE__) . "/messages/$lang/default.php";
            if (is_file($file))
                $messages = include($file);
        }
    }

    if (empty($message))
        return $message;

    if (isset($messages[$message]) && $messages[$message] !== '')
        $message = $messages[$message];

    return $params !== array() ? strtr($message, $params) : $message;
}

function getPreferredLanguage() {
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($n = preg_match_all('/([\w\-]+)\s*(;\s*q\s*=\s*(\d*\.\d*))?/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) > 0) {
        $languages = array();
        for ($i = 0; $i < $n; ++$i)
            $languages[$matches[1][$i]] = empty($matches[3][$i]) ? 1.0 : floatval($matches[3][$i]);
        arsort($languages);
        foreach ($languages as $language => $pref)
            return strtolower(str_replace('-', '_', $language));
    }
    return false;
}

function getServerInfo() {
    $info[] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
    $info[] = '<a href="http://www.easy.lellysinformatica.com/">Easy Framework</a>/' . getFrameworkVersion();
    $info[] = @strftime('%Y-%m-%d %H:%M', time());

    return implode(' ', $info);
}

function renderFile($_file_, $_params_ = array()) {
    extract($_params_);
    require($_file_);
}

$result = 1;  // 1: all pass, 0: fail, -1: pass with warnings

foreach ($requirements as $i => $requirement) {
    if ($requirement[1] && !$requirement[2])
        $result = 0;
    else if ($result > 0 && !$requirement[1] && !$requirement[2])
        $result = -1;
    if ($requirement[4] === '')
        $requirements[$i][4] = '&nbsp;';
}

$lang = getPreferredLanguage();
$viewFile = dirname(__FILE__) . "/views/$lang/index.php";
if (!is_file($viewFile))
    $viewFile = dirname(__FILE__) . '/views/index.php';

renderFile($viewFile, array(
    'requirements' => $requirements,
    'result' => $result,
    'serverInfo' => getServerInfo()
));

