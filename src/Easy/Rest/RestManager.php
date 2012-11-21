<?php

namespace Easy\Rest;

use Easy\Mvc\Controller\Controller;
use Easy\Network\Request;
use Easy\Network\Response;
use Easy\Rest\Metadata\RestMetadata;

class RestManager
{

    public $metadata;
    public $request;
    public $controller;

    public function __construct(Request $request, Controller $controller)
    {
        $this->metadata = new RestMetadata($controller);
        $this->controller = $controller;
        $this->request = $request;
    }

    public function isValidMethod()
    {
        $methods = $this->metadata->getMethodAnnotation($this->request->action);
        if ($methods) {
            //Get the requested method
            $requestedMethod = $this->request->method();
            //If the requested method is in the permited array
            if (in_array($requestedMethod, (array) $methods)) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public function sendResponseCode(Response $response)
    {
        $responseCode = $this->metadata->getCodeAnnotation($this->request->action);
        if ($responseCode) {
            $response->statusCode($responseCode);
        }
    }

    public function formatResult($result)
    {
        $format = $this->metadata->getFormatAnnotation($this->request->action);
        if ($format === 'json') {
            $this->controller->setAutoRender(false);
            $this->controller->RequestHandler->respondAs('json');
            return $this->controller->Json->encode($result);
        } elseif ($format === 'xml') {
            $this->controller->setAutoRender(false);
            $this->controller->RequestHandler->respondAs('xml');
            return $this->controller->Xml->encode($result);
        } else {
            return $result;
        }
    }

}
