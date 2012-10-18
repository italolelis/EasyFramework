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

namespace Easy\View;

use Easy\Controller\Controller;
use Easy\Core\Config;
use Easy\View\Engine\ITemplateEngine;
use Easy\View\Helper\FormHelper;
use Easy\View\Helper\HtmlHelper;
use Easy\View\Helper\SessionHelper;
use Easy\View\HelperCollection;

/**
 * View, the V in the MVC triad. View interacts with Helpers and view variables passed
 * in from the controller to render the results of the controller action.  Often this is HTML,
 * but can also take the form of JSON, XML, PDF's or streaming files.
 *
 * EasyFw uses a two-step-view pattern.  This means that the view content is rendered first,
 * and then inserted into the selected layout.  This also means you can pass data from the view to the
 * layout using `$this->set()`
 * 
 * @since 0.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 * 
 * @property      FormHelper $Form
 * @property      HtmlHelper $Html
 * @property      PaginatorHelper $Paginator
 * @property      SessionHelper $Session
 */
class View
{

    /**
     * @var Controller The controller which control the view
     */
    protected $controller;

    /**
     * @var HelperCollection Helpers collection
     */
    protected $Helpers = array();

    /**
     * @var ITemplateEngine ITemplateEngine object
     */
    protected $engine;

    /**
     * @var array View Config
     */
    protected $config;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;

        $this->config = Config::read('View');
        // Instanciate a Engine
        $this->engine = $this->loadEngine(Config::read('View.engine'));
        // Build the template language
        $this->buildLayouts();
        // Build the template language
        $this->buildElements();

        $this->Helpers = new HelperCollection($this);

        // Loads all associate helpers
        $this->loadHelpers($controller);
    }

    /**
     * Provides backwards compatibility access to the request object properties.
     * Also provides the params alias.
     *
     * @param $name string
     * @return void
     */
    public function __get($name)
    {
        if (isset($this->Helpers->{$name})) {
            $this->{$name} = $this->Helpers->{$name};
            return $this->Helpers->{$name};
        }
    }

    public function loadHelpers($controller)
    {
        $this->Helpers->init($controller);
    }

    /**
     * Gets the current active engine
     * @return ITemplateEngine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getController()
    {
        return $this->controller;
    }

    protected function loadEngine($engine = null)
    {
        if (is_null($engine)) {
            $engine = 'Smarty';
        }
        $factory = new ViewEngineFactory();
        return $factory->build($engine);
    }

    /**
     * Display a view
     * @param $view string The view's name to be show
     * @param $layout string The layout name to be rendered
     */
    function display($view, $layout, $ext = null, $output = true)
    {
        return $this->engine->display($layout, $view, $ext, $output);
    }

    /**
     * Defines a varible which will be passed to the view
     *
     * @param $var string The varible's name
     * @param $value mixed The varible's value
     */
    function set($var, $value)
    {
        $this->engine->set($var, $value);
    }

    /**
     * Build the includes vars for the views.
     * This makes the call more friendly.
     */
    private function buildLayouts()
    {
        if (isset($this->config ["layouts"]) && is_array($this->config ["layouts"])) {
            $layouts = $this->config ["layouts"];
            foreach ($layouts as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Build the includes vars for the views.
     * This makes the call more friendly.
     */
    private function buildElements()
    {
        if (isset($this->config ["elements"]) && is_array($this->config ["elements"])) {
            $elements = $this->config ["elements"];
            foreach ($elements as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

}