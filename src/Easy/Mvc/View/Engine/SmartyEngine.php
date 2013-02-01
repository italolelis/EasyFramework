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

namespace Easy\Mvc\View\Engine;

use Easy\HttpKernel\Kernel;
use Easy\Mvc\Routing\Mapper;
use Easy\Mvc\View\Engine\ITemplateEngine;
use Easy\Utility\Hash;
use Smarty;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class handles the smarty engine 
 * @since 0.1
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class SmartyEngine implements ITemplateEngine {

    /**
     * @var Smarty Smarty Object
     */
    protected $smarty;
    protected $options;

    /**
     * @var Kernel 
     */
    protected $kernel;

    /**
     * Initializes a new instance of the SmartyEngine class.
     * @param array $options The smarty options
     */
    public function __construct(Kernel $kernel, $options = array()) {
        $this->kernel = $kernel;
        $this->options = $options;
        $this->smarty = new Smarty();
        /*
         * This is to mute all expected erros on Smarty and pass to error handler 
         * TODO: Try to get a better implementation 
         */
        Smarty::muteExpectedErrors();
        //Build the template directory
        $this->loadOptions();
    }

    /**
     * @inherited
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @inherited
     */
    public function display($layout, $view, $ext = null, $output = true) {
        list(, $view) = namespaceSplit($view);
        $ext = empty($ext) ? "tpl" : $ext;
        if (!empty($layout)) {
            return $this->smarty->fetch("extends:{$layout}.{$ext}|{$view}.{$ext}", null, null, null, $output);
        } else {
            return $this->smarty->fetch("file:{$view}.{$ext}", null, null, null, $output);
        }
    }

    /**
     * @inherited
     */
    public function set($var, $value) {
        return $this->smarty->assign($var, $value);
    }

    private function loadOptions() {
        $tmpFolder = $this->kernel->getTempDir();
        $cacheDir = $this->kernel->getCacheDir();
        $appDir = $this->kernel->getApplicationRootDir();
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

        //\Easy\Utility\Debugger::dump($defaults["plugins_dir"]);
        //exit();

        $this->options = Hash::merge($defaults, $this->options);

        $this->loadAreasConfigurations();

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

    private function loadAreasConfigurations() {
        $appDir = $this->kernel->getApplicationRootDir();
        $options = array();
        $prefixes = Mapper::getPrefixes();
        foreach ($prefixes as $prefix) {
            $options["areas_template_dir"][$prefix . "Views"] = $appDir . "/Areas/" . $prefix . "/View/Pages";
            $options["areas_template_dir"][$prefix . "Layouts"] = $appDir . "/Areas/" . $prefix . "View/Layouts";
            $options["areas_template_dir"][$prefix . "Elements"] = $appDir . "/Areas/" . $prefix . "View/Elements";
        }

        $this->smarty->addTemplateDir($options["areas_template_dir"]);
    }

    private function checkDir($dir) {
        $fs = new Filesystem();
        $fs->mkdir($dir);
    }

}
