<?php

class ExceptionRender {

    protected $_exception;

    /**
     * @var integer maximum number of source code lines to be displayed. Defaults to 25.
     */
    public $maxSourceLines = 25;

    /**
     * @var integer maximum number of trace source code lines to be displayed. Defaults to 10.
     * @since 1.1.6
     */
    public $maxTraceSourceLines = 10;

    function __construct(Exception $ex) {
        $this->_exception = $ex;
    }

    public function render($view, $data) {
        Config::write('Error.exception', $this->_exception);

        $data['version'] = '<a href="http://www.easy.lellysinformatica.com/">Easy Framework</a>/' . App::getVersion();
        $data['time'] = time();

//        $response = new Response(array('charset' => Config::read('App.encoding')));
//        $response->statusCode($data['code']);
//        $response->send();

        if (App::isDebug()) {
            include CORE . 'Error' . DS . 'templates' . DS . $view . '.php';
        } else {
            include CORE . 'Error' . DS . 'templates' . DS . 'render_error.php';
        }
    }

    protected function _getTemplate() {
        if (App::isDebug()) {
            $template = "Exception";
        } else {
            if ($this->_exception->getCode() === 404) {
                $template = 'notFound';
            } else if ($this->_exception->getCode() === 500) {
                $template = 'serverError';
            }
        }
        return $template;
    }

    public function handleException() {
        $template = $this->_getTemplate();

        if (!Config::read('Exception.customErrors')) {
            if (($trace = $this->getExactTrace($this->_exception)) === null) {
                $fileName = $this->_exception->getFile();
                $errorLine = $this->_exception->getLine();
            } else {
                $fileName = $trace['file'];
                $errorLine = $trace['line'];
            }

            $trace = $this->_exception->getTrace();
            foreach ($trace as $i => $t) {
                if (!isset($t['file']))
                    $trace[$i]['file'] = 'unknown';

                if (!isset($t['line']))
                    $trace[$i]['line'] = 0;

                if (!isset($t['function']))
                    $trace[$i]['function'] = 'unknown';

                unset($trace[$i]['object']);
            }

            $data = array(
                'code' => ($this->_exception instanceof HttpException) ? $this->_exception->getCode() : 500,
                'type' => get_class($this->_exception),
                'errorCode' => $this->_exception->getCode(),
                'message' => $this->_exception->getMessage(),
                'file' => $fileName,
                'line' => $errorLine,
                'trace' => $this->_exception->getTraceAsString(),
                'traces' => $trace,
            );

            $this->render($template, $data);
        }else {
            App::getInstance()->displayExceptions($template);
        }
    }

    /**
     * Returns the exact trace where the problem occurs.
     * @param Exception $exception the uncaught exception
     * @return array the exact trace where the problem occurs
     */
    protected function getExactTrace($exception) {
        $traces = $exception->getTrace();

        foreach ($traces as $trace) {
            // property access exception
            if (isset($trace['function']) && ($trace['function'] === '__get' || $trace['function'] === '__set'))
                return $trace;
        }
        return null;
    }

    /**
     * Converts arguments array to its string representation
     *
     * @param array $args arguments array to be converted
     * @return string string representation of the arguments array
     */
    protected function argumentsToString($args) {
        $count = 0;

        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            $count++;
            if ($count >= 5) {
                if ($count > 5)
                    unset($args[$key]);
                else
                    $args[$key] = '...';
                continue;
            }

            if (is_object($value))
                $args[$key] = get_class($value);
            else if (is_bool($value))
                $args[$key] = $value ? 'true' : 'false';
            else if (is_string($value)) {
                if (strlen($value) > 64)
                    $args[$key] = '"' . substr($value, 0, 64) . '..."';
                else
                    $args[$key] = '"' . $value . '"';
            }
            else if (is_array($value))
                $args[$key] = 'array(' . $this->argumentsToString($value) . ')';
            else if ($value === null)
                $args[$key] = 'null';
            else if (is_resource($value))
                $args[$key] = 'resource';

            if (is_string($key)) {
                $args[$key] = '"' . $key . '" => ' . $args[$key];
            } else if ($isAssoc) {
                $args[$key] = $key . ' => ' . $args[$key];
            }
        }
        $out = implode(", ", $args);

        return $out;
    }

    /**
     * Renders the source code around the error line.
     * @param string $file source file path
     * @param integer $errorLine the error line number
     * @param integer $maxLines maximum number of lines to display
     * @return string the rendering result
     */
    protected function renderSourceCode($file, $errorLine, $maxLines) {
        $errorLine--; // adjust line number to 0-based from 1-based

        $lines = file($file);
        $lineCount = count($lines);

        if ($errorLine < 0 || $lines === false || $lineCount <= $errorLine) {
            return '';
        }

        $halfLines = (int) ($maxLines / 2);
        $beginLine = $errorLine - $halfLines > 0 ? $errorLine - $halfLines : 0;
        $endLine = $errorLine + $halfLines < $lineCount ? $errorLine + $halfLines : $lineCount - 1;
        $lineNumberWidth = strlen($endLine + 1);
        $output = '';

        for ($i = $beginLine; $i <= $endLine; ++$i) {

            $isErrorLine = $i === $errorLine;
            $line = htmlspecialchars(str_replace("\t", '    ', $lines[$i]), ENT_QUOTES, 'UTF-8');
            $code = sprintf("%0{$lineNumberWidth}d %s", $i + 1, $line);

            if (!$isErrorLine) {
                $output.= $code;
            } else {
                $output.= '<span class="error">' . $code . '</span>';
            }
        }
        return '<div class="code"><pre>' . $output . '</pre></div>';
    }

}