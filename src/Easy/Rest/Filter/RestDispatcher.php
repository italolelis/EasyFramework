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

namespace Easy\Rest\Filter;

use Easy\HttpKernel\Event\AfterCallEvent;
use Easy\HttpKernel\Event\FilterResponseEvent;
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Rest\RestManager;
use Symfony\Component\Serializer\Exception\RuntimeException;

class RestDispatcher
{

    /**
     * @var Controller
     */
    private $controller;
    private static $manager;

    public function beforeCall(InitializeEvent $event)
    {
        $this->controller = $event->getController();
        $this->loadManager();

        //If the requested action is annotated with Ajax
        if (static::$manager->isAjax($this->controller->getRequest()->action)) {
            $this->controller->setAutoRender(false);
        }

        if (!static::$manager->isValidMethod()) {
            throw new RuntimeException(__("You can not access this."));
        }
    }

    public function afterCall(AfterCallEvent $event)
    {
        $event->setResult(static::$manager->formatResult($event->getResult()));
    }

    public function afterDispatch(FilterResponseEvent $event)
    {
        static::$manager->sendResponseCode($event->getResponse());
    }

    private function loadManager()
    {
        if (!static::$manager) {
            static::$manager = new RestManager($this->controller);
        }
    }

}