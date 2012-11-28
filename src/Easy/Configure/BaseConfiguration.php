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

use Easy\Core\App;
use Easy\Core\Config;
use Easy\Error\Error;
use Easy\Mvc\Routing\Mapper;
use Easy\Utility\Hash;

class BaseConfiguration implements IConfiguration
{

    public $engine = 'yaml';
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
    }

    public function buildConfigs()
    {
        $this->beforeConfigure();
        $this->loadConfigFiles();
        $this->afterConfigure();
    }

    public function loadConfigFiles()
    {
        foreach ($this->configFiles as $file) {
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
        $timezone = Config::read('App.timezone');
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

        $prefixes = Mapper::prefixes();

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

}