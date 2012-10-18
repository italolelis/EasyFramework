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

namespace Easy\View\Engine\TwigEngine;

use Easy\Collections\Dictionary;
use Easy\Core\Config;
use Easy\Network\Request;
use Easy\Utility\Hash;
use Easy\View\Engine\ITemplateEngine;
use Twig_Environment;
use Twig_Loader_String;

/**
 * This class handles the twig engine 
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class TwigEngine implements ITemplateEngine
{

    /**
     * @var Twig_Environment The Twig object
     */
    protected $twig;
    protected $options = array(
        'debug' => false,
        'cache' => false,
        'strict_variables' => false,
        'optimizations' => -1
    );
    protected $viewVars;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->options = Hash::marge($this->options, Config::read('View.options'));
        $this->viewVars = new Dictionary();

        if ($this->request->prefix) {
            $this->options["template_dir"][] = APP_PATH . DS . "Areas" . DS . $this->request->prefix . DS . "View" . DS . "Pages";
            $this->options["template_dir"][] = APP_PATH . DS . "Areas" . DS . $this->request->prefix . DS . "View" . DS . "Layouts";
            $this->options["template_dir"][] = APP_PATH . DS . "Areas" . DS . $this->request->prefix . DS . "View" . DS . "Elements";
        }

        $loader = new Twig_Loader_String($this->options['template_dir']);
        $this->twig = new Twig_Environment($loader, $this->options);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function display($layout, $view, $ext = null, $output = true)
    {
        list(, $view) = namespaceSplit($view);
        $ext = empty($ext) ? "twig" : $ext;
        $method = $output ? 'display' : 'render';


        if (!empty($layout)) {
            $twigLayout = $this->twig->loadTemplate($layout . "." . $ext);
            $this->viewVars->Add('layout', $twigLayout);
            return $this->twig->{$method}($view . "." . $ext, $this->viewVars->GetArray());
        } else {
            $twigView = $this->twig->loadTemplate($layout . "." . $ext);
            return $twigView->{$method}($view . $ext, $this->viewVars->GetArray());
        }
    }

    public function set($var, $value)
    {
        $this->viewVars->Add($var, $value);
    }

}
