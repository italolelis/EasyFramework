<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\RestBundle;

use Doctrine\Common\Annotations\AnnotationReader;
use Easy\Bundles\RestBundle\Metadata\RestMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestManager
{

    public $metadata;

    /**
     * @var Request
     */
    public $request;
    public $controller;

    public function __construct($controller, $request)
    {
        $this->metadata = new RestMetadata($controller[0], new AnnotationReader());
        $this->controller = $controller;
        $this->request = $request;
    }

    public function isValidMethod()
    {
        $methods = $this->metadata->getMethodAnnotation($this->controller[1]);

        if ($methods) {
            //Get the requested method
            $requestedMethod = $this->request->getMethod();
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
        $responseCode = $this->metadata->getCodeAnnotation($this->controller[1]);
        if ($responseCode) {
            $response->setStatusCode($responseCode);
        }
    }

    public function formatResult($result)
    {
        $format = $this->metadata->getFormatAnnotation($this->controller[1]);
        $returnType = null;

        if (is_array($format)) {

            $accepts = $this->request->accepts();
            foreach ($format as $f) {
                if (in_array($f, $accepts)) {
                    $returnType = $f;
                    break;
                }
            }

            if (!$returnType) {
                $returnType = array_shift($format);
            }
        } else {
            $returnType = $format;
        }

        if ($returnType) {
            $this->request->attributes->set('_auto_render', false);
            $this->controller[0]->RequestHandler->respondAs($returnType);
            $result = $this->controller[0]->get('serializer')->serialize($result, $returnType);
        }

        return $result;
    }

    public function isAjax($action)
    {
        return $this->metadata->isAjax($action);
    }

}
