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

namespace Easy\Mvc\Controller\Component;

use Easy\Mvc\Controller\Component;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Mvc\View\View;
use LogicException;

/**
 * Handles sending emails
 * 
 * @since 1.5
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Email extends Component
{

    public $engine = "\Easy\Mvc\Controller\Component\Email\PhpMailerEngine";
    protected $viewVars;

    public function __construct($engine = null)
    {
        if ($engine !== null) {
            $this->engine = $engine;
        }
    }

    public function load($engine = null)
    {
        if ($engine === null) {
            return $this->loadClass($this->engine);
        }
        return $this->loadClass($engine);
    }

    private function loadClass($engine)
    {
        if (class_exists($engine)) {
            return new $engine();
        } else {
            throw new LogicException(__("Mail engine %s not found", $engine));
        }
    }

    public function initialize(InitializeEvent $event)
    {
        $this->controller = $event->getController();
        $this->viewVars = $this->controller->viewVars;
    }

    public function addVar($name, $value)
    {
        $this->viewVars[$name] = $value;
    }

    public function addVars(array $vars)
    {
        foreach ($vars as $key => $value) {
            $this->addVar($key, $value);
        }
    }

    public function renderViewBody($action, $controller = true, $layout = false)
    {
        if ($controller === true) {
            $controller = $this->controller->getName();
        }

        $view = new View($this->controller);
        //Pass the view vars to view class
        foreach ($this->viewVars as $key => $value) {
            $view->set($key, $value);
        }
        return $view->display("{$controller}/{$action}", $layout, null, false);
    }

}