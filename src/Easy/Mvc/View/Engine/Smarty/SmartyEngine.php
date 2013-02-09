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

namespace Easy\Mvc\View\Engine\Smarty;

use Easy\Mvc\Controller\Controller;
use Easy\Mvc\View\Engine\Engine;
use Easy\Network\Response;
use Easy\Utility\Hash;
use Smarty;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class handles the smarty engine 
 * @since 0.1
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class SmartyEngine extends Engine
{

    /**
     * @var Smarty Smarty Object
     */
    protected $smarty;

    /**
     * Initializes a new instance of the SmartyEngine class.
     *      * @param Controller $controller The controller to be associated with the view
     * @param array $options The options
     */
    public function __construct(Controller $controller, $options = array())
    {
        $this->smarty = new Smarty();
        Smarty::muteExpectedErrors();
        parent::__construct($controller, $options);
        //Build the template directory
        $this->loadOptions();
    }

    /**
     * @inherited
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @inherited
     */
    public function display($view, $layout, $output = true)
    {
        list(, $view) = namespaceSplit($view);
        $ext = "tpl";

        $layout = $this->getLayout($layout);
        if (!empty($layout)) {
            $content = $this->smarty->fetch("extends:{$layout}.{$ext}|{$view}.{$ext}", null, null, null, $output);
        } else {
            $content = $this->smarty->fetch("file:{$view}.{$ext}", null, null, null, $output);
        }

        if ($output === true) {
            $response = new Response();
            // Display the view
            $response->setContent($content);
            return $response;
        } else {
            return $content;
        }
    }

    /**
     * @inherited
     */
    public function set($var, $value)
    {
        return $this->smarty->assign($var, $value);
    }

    private function loadOptions()
    {
        $tmpFolder = $this->kernel->getTempDir();
        $cacheDir = $this->kernel->getCacheDir();
        $appDir = $this->bundle->getPath();
        $rootDir = $this->kernel->getFrameworkDir();

        $defaults = array(
            "template_dir" => array(
                'views' => $appDir . "/View/Pages",
                'layouts' => $appDir . "/View/Layouts",
                'elements' => $appDir . "/View/Elements"
            ),
            "compile_dir" => $tmpFolder . "/views/",
            "cache_dir" => $cacheDir . "/views/",
            "plugins_dir" => array(
                $rootDir . "/Mvc/View/Engine/Smarty/Plugins"
            ),
            "cache" => false
        );

        $this->options = Hash::merge($defaults, $this->options);

        $this->smarty->addTemplateDir($this->options["template_dir"]);
        $this->smarty->addPluginsDir($this->options["plugins_dir"]);

        $this->checkDir($this->options["compile_dir"]);
        $this->smarty->setCompileDir($this->options["compile_dir"]);

        $this->checkDir($this->options["cache_dir"]);
        $this->smarty->setCacheDir($this->options["cache_dir"]);

        if ($this->options['cache']) {
            $this->smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $this->smarty->setCacheLifetime($this->options['cache']['lifetime']);
        }
    }

    private function checkDir($dir)
    {
        $fs = new Filesystem();
        $fs->mkdir($dir);
    }

}
