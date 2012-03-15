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
        }

        $response = new Response(array('charset' => Config::read('App.encoding')));
        $response->statusCode($this->exception->getCode());
        $response->send();

        require_once CORE . 'Error/templates/render_error.php';
    }

}

?>
