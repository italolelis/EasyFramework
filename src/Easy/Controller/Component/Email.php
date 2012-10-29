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

namespace Easy\Controller\Component;

use Easy\Controller\Component;
use Easy\Controller\Controller;
use Easy\View\View;

/**
 * Handles sending emails
 * 
 * @since 1.5
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Email extends Component
{

    protected $engine;
    protected $viewVars;

    public function load($engine = null)
    {
        if ($engine === null) {
            return $this->getEngine($this->engine);
        }
        return $this->getEngine($engine);
    }

    private function getEngine($engine)
    {
        $facotry = new EmailFactory();
        return $facotry->create($engine);
    }

    public function initialize(Controller $controller)
    {
        $this->controller = $controller;
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