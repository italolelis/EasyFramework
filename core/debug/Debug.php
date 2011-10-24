<?php

App::import("Core", array("debug/exceptions"));

class Debug {

    public static function handleErrors($handler = null) {
        if (is_null($handler)) {
            $handler = array('Debug', 'handleError');
        }

        set_error_handler($handler, -1);
        //ini_set('error_log', ROOT . '/log/error.log');
    }

    public static function handleExceptions($handler = null) {
        if (is_null($handler)) {
            $handler = array('Debug', 'handleException');
        }

        set_exception_handler($handler);
    }

    public static function handleError($code, $message, $file, $line) {
        throw new ErrorException($message, 0, $code, $file, $line);
    }

    public static function handleException(Exception $ex) {
        if ($ex instanceof EasyException) {
            App::import("Core", array("debug/exception_render"));
            $renderException = new ExceptionRender($ex);
            $renderException->render($ex);
        } else {
            echo $ex->getMessage();
        }
    }

    public static function log($message) {
        error_log($message);
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

Debug::handleExceptions();