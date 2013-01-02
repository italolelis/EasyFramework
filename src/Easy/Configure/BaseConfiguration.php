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

namespace Easy\Configure;

use Easy\ClassLoader\UniversalClassLoader;
use Easy\Collections\Dictionary;
use Easy\Core\Config;
use Easy\Error\Error;

class BaseConfiguration implements IConfiguration
{

    /**
     * @var string 
     */
    public $engine = 'yaml';

    /**
     * @var Dictionary 
     */
    private $configs;

    /**
     * @var array 
     */
    protected $configFiles = array(
        "application",
        "errors",
        "filters",
        "routes",
        "views"
    );

    public function __construct()
    {
        $this->buildConfigs();
        $this->configs = new Dictionary(Config::read());
        $this->configureApplication();
    }

    /**
     * Gets the configuration engine
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Sets the configuration engine
     * @param string $engine
     */
    public function setEngine($engine)
    {
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
    public function get($value)
    {
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
    public function getEnvironment()
    {
        return $this->get("App.environment");
    }

    /**
     * Check if the application is in debug mode
     * @return bool
     */
    public function isDebug()
    {
        return (bool) $this->get("App.debug");
    }

    private function buildConfigs()
    {
        $this->beforeConfigure($this->configFiles);
        $this->loadConfigFiles($this->configFiles);
        $this->afterConfigure($this->configs);
    }

    /**
     * Loads config files
     * @param array $configs
     */
    public function loadConfigFiles($configs)
    {
        foreach ($configs as $file) {
            Config::load($file, $this->engine);
        }
    }

    /**
     * Configure default application configurations
     * @param string $engine
     */
    private function configureApplication()
    {
        /* Handle the Exceptions and Errors */
        Error::handleExceptions($this->get('Exception'));
        Error::handleErrors($this->get('Error'));

        //Init the app configurations
        $loader = new UniversalClassLoader();
        $loader->registerNamespace($this->get('App.namespace'), dirname(APP_PATH));
        $loader->register();
    }

    public function beforeConfigure($configsFiles)
    {
        return null;
    }

    public function afterConfigure($configs)
    {
        return null;
    }

}