<?php

class ExceptionRender {

    protected $_exception;

    function __construct(EasyException $ex) {
        $this->_exception = $ex;
    }

    public function render() {
        $debug = Config::read("App.debug");
        Config::write('Error.exception', $this->_exception);

        if (Config::read('Exception.customErrors')) {
            return $this->renderUserErrors();
        } else {
            return $this->renderDefaultErrors();
        }
    }

    protected function _getTemplate($debug) {
        if (!empty($debug)) {
            $template = strstr(get_class($this->_exception), "Exception", true);
        } else {
            if ($this->_exception->getCode() == 404) {
                $template = 'badRequest';
            } else if ($this->_exception->getCode() == 500) {
                $template = 'serverError';
            }
        }
        return $template;
    }

    public function renderDefaultErrors() {
        $debug = Config::read("App.debug");


        $details = $this->_exception->getAttributes();
        $template = $this->_getTemplate($debug);

        $response = new Response(array('charset' => Config::read('App.encoding')));
        $response->statusCode($this->_exception->getCode());
        $response->send();

        require_once CORE . 'Error' . DS . 'templates' . DS . 'render_error.php';
    }

    public function renderUserErrors() {
        $template = $this->_getTemplate(Config::read("App.debug"));
        try {
            $request = new Request('Error/' . $template);
            $dispatcher = new Dispatcher ();
            $dispatcher->dispatch(
                    $request, new Response(array('charset' => Config::read('App.encoding'))
            ));
        } catch (EasyException $exc) {
            echo '<h3>Render Custom User Error Problem.</h3>' .
            'Message: ' . $exc->getMessage() . ' </br>' .
            'File: ' . $exc->getFile() . '</br>' .
            'Line: ' . $exc->getLine();
        }
    }

}