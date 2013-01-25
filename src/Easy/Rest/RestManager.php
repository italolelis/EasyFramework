<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Rest;

use Easy\Mvc\Controller\Controller;
use Easy\Network\Response;
use Easy\Rest\Metadata\RestMetadata;

class RestManager
{

    public $metadata;
    public $request;
    public $controller;

    public function __construct(Controller $controller)
    {
        $this->metadata = new RestMetadata($controller);
        $this->controller = $controller;
        $this->request = $controller->getRequest();
    }

    public function isValidMethod()
    {
        $methods = $this->metadata->getMethodAnnotation($this->request->action);
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
        $responseCode = $this->metadata->getCodeAnnotation($this->request->action);
        if ($responseCode) {
            $response->setStatusCode($responseCode);
        }
    }

    public function formatResult($result)
    {
        $format = $this->metadata->getFormatAnnotation($this->request->action);
        $returnType = null;

        if (is_array($format)) {

            $accepts = $this->controller->RequestHandler->accepts();
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
            $this->controller->setAutoRender(false);
            $this->controller->RequestHandler->respondAs($returnType);
            $result = $this->controller->Serializer->encode($result, $returnType);
        }

        return $result;
    }

    public function isAjax($action)
    {
        return $this->metadata->isAjax($action);
    }

}
