<?php

App::uses("EasyLog", "Core/Log");

class Error {

    private static $config;

    public static function handleErrors() {
        self::$config['Error'] = Config::read('Error');
        if (is_null(self::$config['Error'])) {
            self::$config['Error'] = array(
                'handler' => 'Error::handleError',
                'log' => true
            );
        }
        set_error_handler(self::$config['Error']['handler'], (E_ALL | E_STRICT) & ~ E_NOTICE);
    }

    public static function handleExceptions() {
        self::$config['Exception'] = Config::read('Exception');

        if (is_null(self::$config['Exception'])) {
            self::$config['Exception'] = array(
                'handler' => 'Error::handleException',
                'renderer' => 'ExceptionRender',
                'log' => true
            );
        }
        set_exception_handler(self::$config['Exception']['handler']);
    }

    public static function handleError($code, $message, $file, $line) {
        Error::log("Code: $code Message: $message - File: $file on Line: $line");
        throw new ErrorException($message, 0, $code, $file, $line);
    }

    public static function handleException(Exception $ex) {
        $renderer = self::$config['Exception']['renderer'];
        $log = self::$config['Exception']['log'];

        if ($ex instanceof EasyException) {
            App::uses($renderer, 'Core/Error');
            try {
                $renderException = new $renderer($ex);
                $renderException->render($ex);
            } catch (Exception $e) {
                self::handleErrors();
                $message = sprintf("[%s] %s\n%s", get_class($e), $e->getMessage(), $e->getTraceAsString());
                trigger_error($message, E_USER_ERROR);
            }
        } else {
            echo $ex->getMessage();
        }

        if ($log) {
            Error::log(
                    "Message: " . $ex->getMessage() .
                    " Trace: " . $ex->getTraceAsString() .
                    " on File: " . $ex->getFile() .
                    ", Line: " . $ex->getLine());
        }
    }

    public static function setErrorReporting($errorType) {
        return error_reporting($errorType);
    }

    public static function showError($message, $errorType) {
        return trigger_error($message, $errorType);
    }

    public static function log($message) {
        if (Config::read('debug')) {
            EasyLog::write(LOG_ERROR, $message);
        } else {
            EasyLog::write(LOG_WARNING, $message);
        }
    }

}

?>
