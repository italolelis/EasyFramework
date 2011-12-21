<?php

App::uses("ExceptionRender", "Core/Debug");
App::uses("EasyLog", "Core/Log");

class Debug {

    public static function handleErrors($handler = null) {
        if (is_null($handler)) {
            $handler = array('Debug', 'handleError');
        }
        set_error_handler($handler, -1);
    }

    public static function handleExceptions($handler = null) {
        if (is_null($handler)) {
            $handler = array('Debug', 'handleException');
        }
        set_exception_handler($handler);
    }

    public static function handleError($code, $message, $file, $line) {
        Debug::log("Code: $code Message: $message - File: $file on Line: $line");
        throw new ErrorException($message, 0, $code, $file, $line);
    }

    public static function handleException(Exception $ex) {
        Debug::log("Message: " . $ex->getMessage() . " Trace: " . $ex->getTraceAsString() . " on File: " . $ex->getFile() . ", Line: " . $ex->getLine());

        if ($ex instanceof EasyException) {
            $renderException = new ExceptionRender($ex);
            $renderException->render($ex);
        } else {
            echo $ex->getMessage();
        }
    }

    public static function log($message) {
        if (Config::read('debug')) {
            EasyLog::write("debug", $message);
        } else {
            EasyLog::write("warning", $message);
        }
    }

    public static function pr($data) {
        echo '<pre>' . print_r($data, true) . '</pre>';
    }

    public static function dump($data) {
        self::pr(var_export($data, true));
    }

    public static function trace() {
        return debug_backtrace();
    }

}

function pr($data) {
    Debug::pr($data);
}

function dump($data) {
    Debug::dump($data);
}