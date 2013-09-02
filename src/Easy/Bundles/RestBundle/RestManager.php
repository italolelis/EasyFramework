<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\RestBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestManager
{

    /**
     * @var Request
     */
    private $request;
    private $serializer;

    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function isValidMethod($methods)
    {
        if ($methods) {
            //Get the requested method
            $requestedMethod = $this->request->getMethod();
            //If the requested method is in the permited array
            if (in_array($requestedMethod, (array)$methods)) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public function sendResponseCode(Response $response, $responseCode)
    {
        if ($responseCode) {
            $response->setStatusCode($responseCode);
        }
    }

    public function sendContentType(Response $response, $format)
    {
        $type = $this->getType($format);
        $response->headers->set('Content-Type', $type);
    }

    public function formatResult($result, $format)
    {
        $type = $this->getType($format);

        if ($type) {
            $this->request->attributes->set('_auto_render', false);
            $result = $this->serializer->serialize($result, $this->request->getFormat($type));
        }

        return $result;
    }

    protected function getType($format)
    {
        $accepts = $this->request->getAcceptableContentTypes();

        if ($format === null) {
            $returnType = array_shift($accepts);
        } else if (is_array($format)) {
            foreach ($format as $f) {
                $f = $this->request->getMimeType($f);
                if (in_array($f, $accepts)) {
                    $returnType = $f;
                    break;
                }
            }
            if (!$returnType) {
                $returnType = $this->request->getMimeType(array_shift($format));
            }
        } else {
            $returnType = $this->request->getMimeType($format);
        }

        return $returnType;
    }

}
