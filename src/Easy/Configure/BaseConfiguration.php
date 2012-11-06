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

use Easy\Cache\Cache;
use Easy\Core\App;
use Easy\Core\Config;
use Easy\Error\Error;

class BaseConfiguration implements IConfiguration
{

    public $engine = 'yaml';
    public $configFiles = array(
        "application",
        "cache",
        "errors",
        "components",
        "session",
        "filters",
        "log",
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
        //Cache Definitions
        $options = Config::read('Cache.options');
        foreach ($options as $key => $value) {
            Cache::config($key, $value);
        }
        //Locale Definitions
        $timezone = Config::read('App.timezone');
        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }

        //Log Definitions
        $logScopes = Config::read('Log.scopes');
        if (!empty($logScopes)) {
            foreach ($logScopes as $scope => $options) {
                EasyLog::config($scope, $options);
            }
        }

        require CORE . 'Configure' . DS . 'routes.php';

        /* Handle the Exceptions and Errors */
        Error::handleExceptions(Config::read('Exception'));
        Error::handleErrors(Config::read('Error'));
        //Init the app configurations
        App::init();
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