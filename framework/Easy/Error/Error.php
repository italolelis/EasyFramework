<?php

namespace Easy\Error;

use Easy\Core\Config;
use Easy\Core\App;
use Easy\Utility\Debugger;
use Easy\Utility\Hash;
use Easy\Log\Monolog\Logger;
use Easy\Log\Monolog\Handler\StreamHandler;

class Error
{

    public static function handleErrors($options = array())
    {
        $default = array(
            'handler' => 'Easy\Error\Error::handleError',
            'level' => E_ALL,
            'log' => true
        );

        $options = Hash::merge($default, $options);

        Error::setErrorReporting($options['level']);

        Config::write('Error', $options);

        set_error_handler($options['handler'], $options['level']);
    }

    public static function handleExceptions($options = array())
    {
        $default = array(
            'handler' => 'Easy\Error\Error::handleException',
            'renderer' => 'ExceptionRender',
            'customErrors' => false,
            'log' => true
        );

        $options = Hash::merge($default, $options);
        Config::write('Exception', $options);
        set_exception_handler($options['handler']);
    }

    /**
     * Set as the default error handler by CakePHP. Use Config::write('Error.handler', $callback), to use your own
     * error handling methods.  This function will use Debugger to display errors when debug > 0.  And
     * will log errors to Log, when debug == 0.
     *
     * You can use Config::write('Error.level', $value); to set what type of errors will be handled here.
     * Stack traces for errors can be enabled with Config::write('Error.trace', true);
     *
     * @param integer $code Code of error
     * @param string $description Error description
     * @param string $file File on which error occurred
     * @param integer $line Line that triggered the error
     * @param array $context Context
     * @return boolean true if error was handled
     */
    public static function handleError($code, $description, $file = null, $line = null, $context = null)
    {
        if (error_reporting() === 0) {
            return false;
        }
        $errorConfig = Config::read('Error');
        list($error, $log) = static::mapErrorCode($code);
        if ($log === LOG_ERR) {
            return static::handleFatalError($code, $description, $file, $line);
        }

        if (App::isDebug()) {
            $data = array(
                'level' => $log,
                'code' => $code,
                'error' => $error,
                'description' => $description,
                'file' => $file,
                'line' => $line,
                'context' => $context,
                'start' => 2,
                'path' => Debugger::trimPath($file)
            );
            return Debugger::getInstance()->outputError($data);
        } else {
            $message = $error . ' (' . $code . '): ' . $description . ' in [' . $file . ', line ' . $line . ']';
            if (!empty($errorConfig['trace'])) {
                $trace = Debugger::trace(array('start' => 1, 'format' => 'log'));
                $message .= "\nTrace:\n" . $trace . "\n";
            }
            return static::log($message, Logger::CRITICAL, 'error');
        }
    }

    public static function handleException(\Exception $ex)
    {
        $options = Config::read('Exception');

        $renderer = App::classname($options['renderer'], 'Error');

        $renderException = new $renderer($ex);
        $renderException->handleException();

        if ($options['log']) {
            $message = sprintf("[%s] %s\n%s", get_class($ex), $ex->getMessage(), $ex->getTraceAsString());
            static::log($message, Logger::CRITICAL, 'exception');
        }
    }

    /**
     * Generate an error page when some fatal error happens.
     *
     * @param integer $code Code of error
     * @param string $description Error description
     * @param string $file File on which error occurred
     * @param integer $line Line that triggered the error
     * @return boolean
     */
    public static function handleFatalError($code, $description, $file, $line)
    {
        $logMessage = 'Fatal Error (' . $code . '): ' . $description . ' in [' . $file . ', line ' . $line . ']';
        Log::write(LOG_ERR, $logMessage);

        $exceptionHandler = Config::read('Exception.handler');
        if (!is_callable($exceptionHandler)) {
            return false;
        }

        if (ob_get_level()) {
            ob_clean();
        }

        if (App::isDebug()) {
            call_user_func($exceptionHandler, new FatalErrorException($description, 500, $file, $line));
        } else {
            call_user_func($exceptionHandler, new InternalErrorException());
        }
        return false;
    }

    public static function setErrorReporting($errorType = null)
    {
        return error_reporting($errorType);
    }

    public static function showError($message, $errorType = null)
    {
        return trigger_error($message, $errorType);
    }

    public static function log($message, $level, $channel)
    {
        $log = new Logger($channel);
        $log->pushHandler(new StreamHandler(LOGS . 'application.log', $level));
        // add records to the log
        $log->addRecord($level, $message);
    }

    /**
     * Map an error code into an Error word, and log location.
     *
     * @param integer $code Error code to map
     * @return array Array of error word, and log location.
     */
    public static function mapErrorCode($code)
    {
        $error = $log = null;
        switch ($code) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $error = 'Fatal Error';
                $log = LOG_ERR;
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $error = 'Warning';
                $log = LOG_WARNING;
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $error = 'Notice';
                $log = LOG_NOTICE;
                break;
            case E_STRICT:
                $error = 'Strict';
                $log = LOG_NOTICE;
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $error = 'Deprecated';
                $log = LOG_NOTICE;
                break;
        }
        return array($error, $log);
    }

}