<?php

class ExceptionRender {

    protected $exception;

    function __construct(EasyException $ex) {
        $this->exception = $ex;
    }

    function render() {
        $debug = is_null(Config::read("debug")) ? false : Config::read("debug");

        if ($debug) {
            $details = $this->exception->getAttributes();
            $template = strstr(get_class($this->exception), "Exception", true);
        } else {
            $template = 404;
        }

        require_once CORE . 'Error/templates/render_error.php';
    }

}

?>
