<?php

class Debug {

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