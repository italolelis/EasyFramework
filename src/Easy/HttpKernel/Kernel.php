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

namespace Easy\HttpKernel;

use Easy\Collections\Dictionary;
use Easy\Configure\IConfiguration;
use Easy\Core\Config;
use Easy\Error\ErrorHandler;
use Easy\Error\ExceptionHandler;
use Easy\Network\Request;
use ReflectionClass;
use ReflectionObject;

abstract class Kernel implements HttpKernelInterface, IConfiguration {

    /**
     * @var string 
     */
    public $engine = 'yaml';

    /**
     * @var Dictionary 
     */
    protected $configs;

    /**
     * @var array 
     */
    protected $configFiles = array(
        "application",
        "filters",
        "routes",
        "views"
    );
    protected $environment;
    protected $debug;
    protected $errorReportingLevel;
    protected $rootDir;
    protected $applicationRootDir;
    protected $frameworkDir;

    const VERSION = '2.0.0';
    const VERSION_ID = '20000';
    const MAJOR_VERSION = '2';
    const MINOR_VERSION = '0';
    const RELEASE_VERSION = '0';
    const EXTRA_VERSION = '';

    public function __construct($environment, $debug) {
        $this->environment = $environment;
        $this->debug = (boolean) $debug;
        $this->loadConfigFiles($this->configFiles);
        $this->configs = new Dictionary(Config::read());
        $this->rootDir = $this->getRootDir();
        $this->applicationRootDir = $this->getApplicationRootDir();
        $this->frameworkDir = $this->getFrameworkDir();
        $this->boot();
    }

    /**
     * Gets the configuration engine
     * @return string
     */
    public function getEngine() {
        return $this->engine;
    }

    /**
     * Sets the configuration engine
     * @param string $engine
     */
    public function setEngine($engine) {
        $this->engine = $engine;
    }

    /**
     * Gets an value from configs based on provided key. You can use namespaced config keys like
     * <code>
     * $config->get(namespace.foo);
     * $config->get(namespace.bar);
     * $config->get(namespace);
     * </code>
     * @param string $value
     * @return null
     */
    public function get($value) {
        $pointer = $this->configs->GetArray();
        foreach (explode('.', $value) as $key) {
            if (isset($pointer[$key])) {
                $pointer = $pointer[$key];
            } else {
                return null;
            }
        }
        return $pointer;
    }

    /**
     * Gets the application environment
     * @return string
     */
    public function getEnvironment() {
        return $this->environment;
    }

    /**
     * Check if the application is in debug mode
     * @return bool
     */
    public function isDebug() {
        return $this->debug;
    }

    /**
     * Configure default application configurations
     * @param string $engine
     */
    private function boot() {
        if ($this->debug) {
            ini_set('display_errors', 1);
            error_reporting(-1);

            ErrorHandler::register($this->errorReportingLevel);
            if ('cli' !== php_sapi_name()) {
                ExceptionHandler::register();
            }
        } else {
            ini_set('display_errors', 0);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request) {
        $httpKernel = new HttpKernel($this);
        return $httpKernel->handle($request);
    }

    public function loadConfigFiles($configs) {
        foreach ($configs as $file) {
            Config::load($file, $this->engine);
        }
    }

    /**
     * Gets the application root dir
     * @return string
     */
    public function getApplicationRootDir() {
        if (null === $this->applicationRootDir) {
            $r = new ReflectionObject($this);
            $this->applicationRootDir = $this->getRecursiveDirname($r->getFileName(), 1);
        }

        return $this->applicationRootDir;
    }

    /**
     * Gets the package root dir
     * @return string
     */
    public function getRootDir() {
        return dirname($this->applicationRootDir);
    }

    /**
     * Gets the framework root dir
     * @return string
     */
    public function getFrameworkDir() {
        if (null === $this->frameworkDir) {
            $r = new ReflectionClass(get_parent_class($this));
            $this->frameworkDir = $this->getRecursiveDirname($r->getFileName(), 2);
        }

        return $this->frameworkDir;
    }

    /**
     * Recursivly gets the dirname of a directory
     * @param string $dir The dir name
     * @param integer $deep The deep of recursive search
     * @param integer $current The current deepth of recursive search
     * @return string
     */
    protected function getRecursiveDirname($dir, $deep, $current = 0) {
        if ($deep !== $current) {
            return $this->getRecursiveDirname(dirname($dir), $deep, $current + 1);
        }
        return str_replace('\\', '/', $dir);
    }

    /**
     * Gets the temp dir
     * @return string
     */
    public function getTempDir() {
        return $this->rootDir . "/tmp";
    }

    /**
     * Gets the cache dir
     * @return string
     */
    public function getCacheDir() {
        return $this->getTempDir() . '/cache';
    }

    /**
     * Gets the logs dir
     * @return string
     */
    public function getLogDir() {
        return $this->getTempDir() . '/logs';
    }

}
