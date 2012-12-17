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
use Easy\Mvc\Routing\Mapper;
use Easy\Utility\Hash;

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
        "components",
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

    public function getEngine()
    {
        return $this->engine;
    }

    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

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

    /**
     * Check if the application use a database connection
     * @return bool
     */
    public function useDatabase()
    {
        return (bool) $this->get("App.useDatabase");
    }

    /**
     * Gets the application timezone
     * @return string
     */
    public function getTimezone()
    {
        return $this->get("App.timezone");
    }

    public function buildConfigs()
    {
        $this->beforeConfigure($this->configFiles);
        $this->loadConfigFiles($this->configFiles);
        $this->afterConfigure($this->configs);
    }

    public function loadConfigFiles($configs)
    {
        foreach ($configs as $file) {
            Config::load($file, $this->engine);
        }
    }

    /**
     * Loads core config file
     * @param string $engine
     */
    private function configureApplication()
    {
        //Locale Definitions
        $timezone = $this->getTimezone();
        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }

        $this->configureRoutes();

        /* Handle the Exceptions and Errors */
        Error::handleExceptions($this->get('Exception'));
        Error::handleErrors($this->get('Error'));

        //Init the app configurations
        $loader = new UniversalClassLoader();
        $loader->registerNamespace($this->get('App.namespace'), dirname(APP_PATH));
        $loader->register();
    }

    private function configureRoutes()
    {
        $connects = $this->get('Routing.connect');
        if (!empty($connects)) {
            foreach ($connects as $url => $route) {
                $options = Hash::arrayUnset($route, 'options');
                Mapper::connect($url, $route, $options);
            }
        }

        $mapResources = $this->get('Routing.mapResources');
        if (!empty($mapResources)) {
            foreach ($mapResources as $resource => $options) {
                if (is_array($options)) {
                    foreach ($options as $k => $v) {
                        $resource = $k;
                        $options = $v;
                    }
                } else {
                    $resource = $options;
                    $options = array();
                }
                Mapper::mapResources($resource, $options);
            }
        }

        $parseExtensions = $this->get('Routing.parseExtensions');
        if (!empty($parseExtensions)) {
            Mapper::parseExtensions($parseExtensions);
        }

        $prefixes = Mapper::getPrefixes();

        foreach ($prefixes as $prefix) {
            $params = array('prefix' => $prefix);
            $indexParams = $params + array('action' => 'index');
            Mapper::connect("/{$prefix}/:controller", $indexParams);
            Mapper::connect("/{$prefix}/:controller/:action/*", $params);
        }
        Mapper::connect('/:controller', array('action' => 'index'));
        Mapper::connect('/:controller/:action/*');

        unset($params, $indexParams, $prefix, $prefixes);
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