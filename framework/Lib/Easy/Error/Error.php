<?php

App::uses("EasyLog", "Log");
App::uses("Hash", "Utility");

class Error {

    public static function handleErrors($options = array()) {
        $default = array(
            'handler' => 'Error::handleError',
            'level' => E_ALL,
            'log' => true
        );

        $options = Hash::merge($default, $options);
        Config::write('Error', $options);

        set_error_handler($options['handler'], $options['level']);
    }

    public static function handleExceptions($options = array()) {
        $default = array(
            'handler' => 'Error::handleException',
            'renderer' => 'ExceptionRender',
            'customErrors' => false,
            'log' => true
        );

        $options = Hash::merge($default, $options);
        Config::write('Exception', $options);

        set_exception_handler($options['handler']);
    }

    public static function handleError($code, $message, $file, $line) {
        Error::log("Code: $code Message: $message - File: $file on Line: $line");
        throw new ErrorException($message, 0, $code, $file, $line);
    }

    public static function handleException(Exception $ex) {
        $options = Config::read('Exception');

        $renderer = $options['renderer'];
        App::uses($renderer, 'Error');

        $renderException = new $renderer($ex);
        $renderException->handleException();

        if ($options['log']) {
            Error::log(
                    "Message: " . $ex->getMessage() .
                    " | Trace: " . $ex->getTrace() .
                    " | File: " . $ex->getFile() .
                    " | Line: " . $ex->getLine()
            );
        }
    }

    public static function setErrorReporting($errorType = null) {
        return error_reporting($errorType);
    }

    public static function showError($message, $errorType = null) {
        return trigger_error($message, $errorType);
    }

    public static function log($message) {
        return EasyLog::write(LOG_ERR, $message);
    }

}