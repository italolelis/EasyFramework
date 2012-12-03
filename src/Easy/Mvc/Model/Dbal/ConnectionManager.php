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

namespace Easy\Mvc\Model\Dbal;

use Easy\Core\Object;
use Easy\Model\Dbal\Exceptions\MissingConnectionException;

/**
 * The EntityManager is the central access point to ORM functionality.
 * 
 * @since 1.5
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class ConnectionManager extends Object
{

    /**
     * Holds a loaded instance of the Connections object
     *
     * @var DATABASE_CONFIG
     */
    private static $config = array();
    private static $init = false;

    /**
     * Holds instances DataSource objects
     *
     * @var array
     */
    private static $datasources = array();

    protected static function init($configs)
    {
        static::$config = $configs;
        static::$init = true;
    }

    /**
     * Gets the list of available DataSource connections
     * This will only return the datasources instantiated by this manager
     *
     * @return array List of available connections
     * @throws MissingConnectionException, MissingDataSourceException
     */
    public static function getDriver($configs, $environment, $dbConfig = null)
    {
        if (!static::$init) {
            static::init($configs);
        }

        if (!empty(static::$datasources[$dbConfig])) {
            return static::$datasources[$dbConfig];
        }

        if (isset(static::$config[$environment][$dbConfig])) {
            $config = static::$config[$environment][$dbConfig];
        } else {
            throw new MissingConnectionException(__('Database connection "%s" is missing, or could not be created.', $dbConfig));
        }

        $factory = new DriverFactory($config);
        $class = $factory->build($config['driver']);
        return static::$datasources[$dbConfig] = $class;
    }

}