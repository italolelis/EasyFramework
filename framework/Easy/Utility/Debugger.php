<?php

/**
 * FROM CAKEPHP
 * 
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 0.5
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Utility;

use Easy\Utility\Hash;
use Easy\Log\Monolog\Logger;
use Easy\Utility\String;
use Easy\Log\Monolog\Handler\StreamHandler;

/**
 * Provide custom logging and error handling.
 *
 * Debugger overrides PHP's default error handling to provide stack traces and enhanced logging
 *
 * @package  Easy.Utility
 */
class Debugger
{

    /**
     * The current output format.
     *
     * @var string
     */
    protected static $outputFormat = 'js';

    /**
     * Templates used when generating trace or error strings.  Can be global or indexed by the format
     * value used in $_outputFormat.
     *
     * @var string
     */
    protected static $templates = array(
        'log' => array(
            'trace' => '{:reference} - {:path}, line {:line}',
            'error' => "{:error} ({:code}): {:description} in [{:file}, line {:line}]"
        ),
        'js' => array(
            'error' => '',
            'info' => '',
            'trace' => '<pre class="stack-trace">{:trace}</pre>',
            'code' => '',
            'context' => '',
            'links' => array()
        ),
        'html' => array(
            'trace' => '<pre class="cake-error trace"><b>Trace</b> <p>{:trace}</p></pre>',
            'context' => '<pre class="cake-error context"><b>Context</b> <p>{:context}</p></pre>'
        ),
        'txt' => array(
            'error' => "{:error}: {:code} :: {:description} on line {:line} of {:path}\n{:info}",
            'code' => '',
            'info' => ''
        ),
        'base' => array(
            'traceLine' => '{:reference} - {:path}, line {:line}',
            'trace' => "Trace:\n{:trace}\n",
            'context' => "Context:\n{:context}\n",
        ),
        'log' => array(),
    );

    /**
     * Recursively formats and outputs the contents of the supplied variable.
     *
     *
     * @param mixed $var the variable to dump
     * @return void
     * @see Debugger::exportVar()
     */
    public static function dump($var, $hightlight = null)
    {
        $dump = self::exportVar($var);
        if ($hightlight) {
            $dump = static::highlight($var);
        }
        return pr($dump);
    }

    /**
     * Creates an entry in the log file.  The log entry will contain a stack trace from where it was called.
     * as well as export the variable using exportVar. By default the log is written to the debug log.
     *
     * @param mixed $var Variable or content to log
     * @param integer $level type of log to use. Defaults to LOG_DEBUG
     * @return void
     * @link http://book.cakephp.org/2.0/en/development/debugging.html#Debugger::log
     */
    public static function log($var, $level = Logger::DEBUG)
    {
        $source = self::trace(array('start' => 1)) . "\n";

        $log = new Logger('debug');
        $log->pushHandler(new StreamHandler());
        $log->addRecord($level, $source . self::exportVar($var));
    }

    /**
     * Outputs a stack trace based on the supplied options.
     *
     * ### Options
     *
     * - `depth` - The number of stack frames to return. Defaults to 999
     * - `format` - The format you want the return.  Defaults to the currently selected format.  If
     *    format is 'array' or 'points' the return will be an array.
     * - `args` - Should arguments for functions be shown?  If true, the arguments for each method call
     *   will be displayed.
     * - `start` - The stack frame to start generating a trace from.  Defaults to 0
     *
     * @param array $options Format for outputting stack trace
     * @return mixed Formatted stack trace
     */
    public static function trace($options = array())
    {
        $defaults = array(
            'depth' => 999,
            'format' => static::$outputFormat,
            'args' => false,
            'start' => 0,
            'scope' => null,
            'exclude' => array('call_user_func_array', 'trigger_error')
        );
        $options = Hash::merge($defaults, $options);

        $backtrace = debug_backtrace();
        $count = count($backtrace);
        $back = array();

        $_trace = array(
            'line' => '??',
            'file' => '[internal]',
            'class' => null,
            'function' => '[main]'
        );

        for ($i = $options['start']; $i < $count && $i < $options['depth']; $i++) {
            $trace = array_merge(array('file' => '[internal]', 'line' => '??'), $backtrace[$i]);
            $signature = $reference = '[main]';

            if (isset($backtrace[$i + 1])) {
                $next = array_merge($_trace, $backtrace[$i + 1]);
                $signature = $reference = $next['function'];

                if (!empty($next['class'])) {
                    $signature = $next['class'] . '::' . $next['function'];
                    $reference = $signature . '(';
                    if ($options['args'] && isset($next['args'])) {
                        $args = array();
                        foreach ($next['args'] as $arg) {
                            $args[] = Debugger::exportVar($arg);
                        }
                        $reference .= join(', ', $args);
                    }
                    $reference .= ')';
                }
            }
            if (in_array($signature, $options['exclude'])) {
                continue;
            }
            if ($options['format'] == 'points' && $trace['file'] != '[internal]') {
                $back[] = array('file' => $trace['file'], 'line' => $trace['line']);
            } elseif ($options['format'] == 'array') {
                $back[] = $trace;
            } else {
                if (isset(static::$templates[$options['format']]['traceLine'])) {
                    $tpl = static::$templates[$options['format']]['traceLine'];
                } else {
                    $tpl = static::$templates['base']['traceLine'];
                }
                $trace['path'] = self::trimPath($trace['file']);
                $trace['reference'] = $reference;
                unset($trace['object'], $trace['args']);
                $back[] = String::insert($tpl, $trace, array('before' => '{:', 'after' => '}'));
            }
        }

        if ($options['format'] == 'array' || $options['format'] == 'points') {
            return $back;
        }
        return implode("\n", $back);
    }

    /**
     * Shortens file paths by replacing the application base path with 'APP', and the CakePHP core
     * path with 'CORE'.
     *
     * @param string $path Path to shorten
     * @return string Normalized path
     */
    public static function trimPath($path)
    {
        if (!defined('CORE') || !defined('APP_PATH')) {
            return $path;
        }

        if (strpos($path, APP_PATH) === 0) {
            return str_replace(APP_PATH, 'App' . DS, $path);
        } elseif (strpos($path, CORE) === 0) {
            return str_replace(CORE, 'Easy', $path);
        }

        if (strpos($path, CORE) === 0) {
            return str_replace($corePath, 'Easy' . DS, $path);
        }
        return $path;
    }

    /**
     * Takes a processed array of data from an error and displays it in the chosen format.
     *
     * @param string $data
     * @return void
     */
    public static function outputError($data)
    {
        $defaults = array(
            'level' => 0,
            'error' => 0,
            'code' => 0,
            'description' => '',
            'file' => '',
            'line' => 0,
            'context' => array(),
            'start' => 2,
        );
        $data += $defaults;

        $files = static::trace(array('start' => $data['start'], 'format' => 'points'));
        $code = '';
        if (isset($files[0]['file'])) {
            $code = static::excerpt($files[0]['file'], $files[0]['line'] - 1, 1);
        }
        $trace = static::trace(array('start' => $data['start'], 'depth' => '20'));
        $insertOpts = array('before' => '{:', 'after' => '}');
        $context = array();
        $links = array();
        $info = '';

        foreach ((array) $data['context'] as $var => $value) {
            $context[] = "\${$var}\t=\t" . static::exportVar($value, 1);
        }

        switch (static::$outputFormat) {
            case 'log':
                static::log(compact('context', 'trace') + $data);
                return;
        }

        $data['trace'] = $trace;
        $data['id'] = 'cakeErr' . uniqid();
        $tpl = array_merge(static::$templates['base'], static::$templates[static::$outputFormat]);
        $insert = array('context' => join("\n", $context)) + $data;

        $detect = array('context');

        if (isset($tpl['links'])) {
            foreach ($tpl['links'] as $key => $val) {
                if (in_array($key, $detect) && empty($insert[$key])) {
                    continue;
                }
                $links[$key] = String::insert($val, $insert, $insertOpts);
            }
        }

        foreach (array('code', 'context', 'trace') as $key) {
            if (empty($$key) || !isset($tpl[$key])) {
                continue;
            }
            if (is_array($$key)) {
                $$key = join("\n", $$key);
            }
            $info .= String::insert($tpl[$key], compact($key) + $insert, $insertOpts);
        }
        $links = join(' ', $links);
        unset($data['context']);
        if (isset($tpl['callback']) && is_callable($tpl['callback'])) {
            return call_user_func($tpl['callback'], $data, compact('links', 'info'));
        }
        echo String::insert($tpl['error'], compact('links', 'info') + $data, $insertOpts);
    }

    /**
     * Grabs an excerpt from a file and highlights a given line of code.
     *
     * Usage:
     *
     * `Debugger::excerpt('/path/to/file', 100, 4);`
     *
     * The above would return an array of 8 items. The 4th item would be the provided line,
     * and would be wrapped in `<span class="code-highlight"></span>`.  All of the lines
     * are processed with highlight_string() as well, so they have basic PHP syntax highlighting
     * applied.
     *
     * @param string $file Absolute path to a PHP file
     * @param integer $line Line number to highlight
     * @param integer $context Number of lines of context to extract above and below $line
     * @return array Set of lines highlighted
     * @see http://php.net/highlight_string
     */
    public static function excerpt($file, $line, $context = 2)
    {
        $lines = array();
        if (!file_exists($file)) {
            return array();
        }
        $data = @explode("\n", file_get_contents($file));

        if (empty($data) || !isset($data[$line])) {
            return;
        }
        for ($i = $line - ($context + 1); $i < $line + $context; $i++) {
            if (!isset($data[$i])) {
                continue;
            }
            $string = str_replace(array("\r\n", "\n"), "", self::highlight($data[$i]));
            if ($i == $line) {
                $lines[] = '<span class="code-highlight">' . $string . '</span>';
            } else {
                $lines[] = $string;
            }
        }
        return $lines;
    }

    /**
     * Wraps the highlight_string funciton in case the server API does not
     * implement the function as it is the case of the HipHop interpreter
     *
     * @param string $str the string to convert
     * @return string
     */
    protected static function highlight($str)
    {
        return highlight_string("<?php\n" . $str, true);
    }

    /**
     * Converts a variable to a string for debug output.
     *
     * *Note:* The following keys will have their contents
     * replaced with `*****`:
     *
     *  - password
     *  - login
     *  - host
     *  - database
     *  - port
     *  - prefix
     *  - schema
     *
     * This is done to protect database credentials, which could be accidentally
     * shown in an error message if CakePHP is deployed in development mode.
     *
     * @param string $var Variable to convert
     * @param integer $depth The depth to output to. Defaults to 3.
     * @return string Variable as a formatted string
     */
    public static function exportVar($var, $depth = 3)
    {
        return self::export($var, $depth, 0);
    }

    /**
     * Protected export function used to keep track of indentation and recursion.
     *
     * @param mixed $var The variable to dump.
     * @param integer $depth The remaining depth.
     * @param integer $indent The current indentation level.
     * @return string The dumped variable.
     */
    protected static function export($var, $depth, $indent)
    {
        switch (self::getType($var)) {
            case 'boolean':
                return ($var) ? 'true' : 'false';
                break;
            case 'integer':
                return '(int) ' . $var;
            case 'float':
                return '(float) ' . $var;
                break;
            case 'string':
                if (trim($var) == '') {
                    return "''";
                }
                return "'" . $var . "'";
                break;
            case 'array':
                return self::_array($var, $depth - 1, $indent + 1);
                break;
            case 'resource':
                return strtolower(gettype($var));
                break;
            case 'null':
                return 'null';
            default:
                return self::object($var, $depth - 1, $indent + 1);
                break;
        }
    }

    /**
     * Export an array type object.  Filters out keys used in datasource configuration.
     *
     * The following keys are replaced with ***'s
     *
     * - password
     * - login
     * - host
     * - database
     * - port
     * - prefix
     * - schema
     *
     * @param array $var The array to export.
     * @param integer $depth The current depth, used for recursion tracking.
     * @param integer $indent The current indentation level.
     * @return string Exported array.
     */
    protected static function _array(array $var, $depth, $indent)
    {
        $secrets = array(
            'password' => '*****',
            'login' => '*****',
            'host' => '*****',
            'database' => '*****',
            'port' => '*****',
            'prefix' => '*****',
            'schema' => '*****'
        );
        $replace = array_intersect_key($secrets, $var);
        $var = $replace + $var;

        $out = "array(";
        $n = $break = $end = null;
        if (!empty($var)) {
            $n = "\n";
            $break = "\n" . str_repeat("\t", $indent);
            $end = "\n" . str_repeat("\t", $indent - 1);
        }
        $vars = array();

        if ($depth >= 0) {
            foreach ($var as $key => $val) {
                $vars[] = $break . self::exportVar($key) .
                        ' => ' .
                        self::export($val, $depth - 1, $indent);
            }
        }
        return $out . implode(',', $vars) . $end . ')';
    }

    /**
     * Handles object to string conversion.
     *
     * @param string $var Object to convert
     * @param integer $depth The current depth, used for tracking recursion.
     * @param integer $indent The current indentation level.
     * @return string
     * @see Debugger::exportVar()
     */
    protected static function object($var, $depth, $indent)
    {
        $out = '';
        $props = array();

        $className = get_class($var);
        $out .= 'object(' . $className . ') {';

        if ($depth > 0) {
            $end = "\n" . str_repeat("\t", $indent - 1);
            $break = "\n" . str_repeat("\t", $indent);
            $objectVars = get_object_vars($var);
            foreach ($objectVars as $key => $value) {
                $value = self::export($value, $depth - 1, $indent);
                $props[] = "$key => " . $value;
            }
            $out .= $break . implode($break, $props) . $end;
        }
        $out .= '}';
        return $out;
    }

    /**
     * Get the type of the given variable. Will return the classname
     * for objects.
     *
     * @param mixed $var The variable to get the type of
     * @return string The type of variable.
     */
    public static function getType($var)
    {
        if (is_object($var)) {
            return get_class($var);
        }
        if (is_null($var)) {
            return 'null';
        }
        if (is_string($var)) {
            return 'string';
        }
        if (is_array($var)) {
            return 'array';
        }
        if (is_int($var)) {
            return 'integer';
        }
        if (is_bool($var)) {
            return 'boolean';
        }
        if (is_float($var)) {
            return 'float';
        }
        if (is_resource($var)) {
            return 'resource';
        }
        return 'unknown';
    }

}
