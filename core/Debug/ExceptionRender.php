<?php

class ExceptionRender {

    protected $exception;

    function __construct(EasyException $ex) {
        $this->exception = $ex;
    }

    function render() {
        $debug = is_null(Config::read("debug")) ? false : Config::read("debug");
        if ($debug) {
            $error = $this->exception->getMessage();
            $details = $this->exception->getDetails();
        } else {
            $error = 404;
        }
        require_once CORE . 'debug/templates/render_error.php';
    }

}

?>
