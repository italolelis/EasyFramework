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

use ArrayAccess;
use Easy\Collections\Dictionary;
use Easy\Core\App;
use Easy\Core\Config;
use Easy\Error\Error;
use Easy\Mvc\Routing\Mapper;
use Easy\Utility\Hash;

class BaseConfiguration implements IConfiguration, ArrayAccess
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
    public $configFiles = array(
        "application",
        "errors",
        "components",
        "filters",
        "routes",
        "views"
    );

    public function __construct()
    {
        App::build();
        $this->buildConfigs();
        $this->configureApplication();
        $this->configureDatabase();
        $this->configs = new Dictionary(Config::read());
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * Gets the application environment
     * @return string
     */
    public function getEnvironment()
    {
        return $this->configs["App"]["environment"];
    }

    /**
     * Check if the application is in debug mode
     * @return bool
     */
    public function isDebug()
    {
        return $this->configs["App"]["debug"];
    }

    /**
     * Gets the application timezone
     * @return string
     */
    public function getTimezone()
    {
        return $this->configs["App"]["timezone"];
    }

    public function buildConfigs()
    {
        $this->beforeConfigure();
        $this->loadConfigFiles($this->configFiles);
        $this->afterConfigure();
    }

    public function loadConfigFiles($configs)
    {
        foreach ($configs as $file) {
            Config::load($file, $this->engine);
        }
    }

    public function configureDatabase()
    {
        $useDatabase = Config::read("App.useDatabase");
        if ($useDatabase == true) {
            Config::load('database', $this->engine);
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
        Error::handleExceptions(Config::read('Exception'));
        Error::handleErrors(Config::read('Error'));
        //Init the app configurations
        App::init();
    }

    private function configureRoutes()
    {
        $connects = Config::read('Routing.connect');
        if (!empty($connects)) {
            foreach ($connects as $url => $route) {
                $options = Hash::arrayUnset($route, 'options');
                Mapper::connect($url, $route, $options);
            }
        }

        $mapResources = Config::read('Routing.mapResources');
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

        $parseExtensions = Config::read('Routing.parseExtensions');
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

    public function beforeConfigure()
    {
        return null;
    }

    public function afterConfigure()
    {
        return null;
    }

    public function offsetExists($offset)
    {
        return $this->configs->contains($offset);
    }

    public function offsetGet($offset)
    {
        return $this->configs->getItem($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->configs->add($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->configs->remove($offset);
    }

}