<?php

namespace Easy\Error;

use Easy\Core\App;
use Easy\Core\Config;
use Easy\Network\Request;
use Easy\Network\Response;
use Easy\Mvc\Routing\Dispatcher;
use Exception;
use HttpException;

class ExceptionRender
{

    protected $exception;

    /**
     * @var integer maximum number of source code lines to be displayed. Defaults to 25.
     */
    public $maxSourceLines = 25;
    public $content;

    /**
     * @var integer maximum number of trace source code lines to be displayed. Defaults to 10.
     * @since 1.1.6
     */
    public $maxTraceSourceLines = 10;

    public function __construct(Exception $ex)
    {
        $this->exception = $ex;
    }

    public function render($view, $data)
    {
        $data['version'] = '<a href="http://www.easyframework.net/">Easy Framework</a>/' . App::getVersion();
        $data['time'] = time();

        if (App::isDebug()) {
            $this->content = include CORE . 'Error' . DS . 'templates' . DS . $view . '.php';
        } else {
            $this->content = include CORE . 'Error' . DS . 'templates' . DS . 'render_error.php';
        }
    }

    protected function getTemplate()
    {
        if (App::isDebug()) {
            $template = "Exception";
        } else {
            if ($this->exception->getCode() === 500) {
                $template = 'serverError';
            } else {
                $template = 'notFound';
            }
        }
        return $template;
    }

    public function handleException()
    {
        $template = $this->getTemplate();

        if (!Config::read('Exception.customErrors')) {
            if (($trace = $this->getExactTrace($this->exception)) === null) {
                $fileName = $this->exception->getFile();
                $errorLine = $this->exception->getLine();
            } else {
                $fileName = $trace['file'];
                $errorLine = $trace['line'];
            }

            $trace = $this->exception->getTrace();
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
                'code' => ($this->exception instanceof HttpException) ? $this->exception->getCode() : 500,
                'type' => get_class($this->exception),
                'errorCode' => $this->exception->getCode(),
                'message' => $this->exception->getMessage(),
                'file' => $fileName,
                'line' => $errorLine,
                'trace' => $this->exception->getTraceAsString(),
                'traces' => $trace,
            );

            header(":", true, $this->exception->getCode());
            //http_response_code($this->_exception->getCode()); //only php 5.4
            $this->render($template, $data);
            echo $this->content;
        }else {
            $this->_handleCustomException();
        }
    }

    protected function _handleCustomException()
    {
        try {
            $template = $this->getTemplate();
            $request = new Request('Error/' . $template);
            $response = new Response(array('charset' => Config::read('App.encoding')));
            $response->statusCode($this->exception->getCode());
            $dispatcher = new Dispatcher();
            $dispatcher->dispatch(
                    $request, $response
            );
        } catch (Exception $exc) {
            echo '<h3>Render Custom User Error Problem.</h3>' .
            'Message: ' . $exc->getMessage() . ' <br>' .
            'File: ' . $exc->getFile() . '<br>' .
            'Line: ' . $exc->getLine();
        }
    }

    /**
     * Returns the exact trace where the problem occurs.
     * @param Exception $exception the uncaught exception
     * @return array the exact trace where the problem occurs
     */
    protected function getExactTrace($exception)
    {
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
    protected function argumentsToString($args)
    {
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
    protected function renderSourceCode($file, $errorLine, $maxLines)
    {
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