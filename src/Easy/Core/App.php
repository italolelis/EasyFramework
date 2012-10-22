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

namespace Easy\Core;

use Easy\Core\Config;
use Easy\Utility\Hash;
use Easy\Error\Error;

/**
 * App is responsible for path management, class location and class loading.
 * 
 * @since 0.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class App
{

    /**
     * Maps an old style class type to the corresponding package
     * @var array
     */
    public static $legacy = array();

    /**
     * Is the Application on debug mode?
     * @var bool
     */
    public static function isDebug()
    {
        return Config::read('App.debug');
    }

    /**
     * Is the Application on debug mode?
     * @var bool
     */
    public static function getEnvironment()
    {
        return getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : Config::read('App.environment');
    }

    /**
     * Obtêm a versão do core
     * @return string 
     */
    public static function getVersion()
    {
        return "2.0.0-beta3";
    }

    /**
     * Sets up each package location on the file system. You can configure multiple search paths
     * for each package, those will be used to look for files one folder at a time in the specified order
     * All paths should be terminated with a Directory separator
     *
     * Usage:
     *
     * `App::build(array(Model' => array('/a/full/path/to/models/'))); will setup a new search path for the Model package`
     *
     * `App::build(array('Model' => array('/path/to/models/')), App::RESET); will setup the path as the only valid path for searching models`
     *
     * `App::build(array('View/Helper' => array('/path/to/helpers/', '/another/path/'))); will setup multiple search paths for helpers`
     *
     * If reset is set to true, all loaded plugins will be forgotten and they will be needed to be loaded again.
     *
     * @param array $paths associative array with package names as keys and a list of directories for new search paths
     * @return void
     */
    public static function build($paths = array())
    {
        self::$legacy = Hash::merge(array(
                    //App Rotes
                    "Areas" => array(
                        APP_PATH . "Areas",
                        CORE . "Areas"
                    ),
                    "Config" => array(
                        APP_PATH . "Config",
                        CORE . "Config"
                    ),
                    "Locale" => array(
                        APP_PATH . "Locale"
                    ),
                    "View" => array(
                        APP_PATH . "View" . DS . "Pages",
                        CORE . "View"
                    ),
                    "Layout" => array(
                        APP_PATH . "View" . DS . "Layouts",
                        CORE . "View" . DS . "Layouts"
                    ),
                    "Element" => array(
                        APP_PATH . "View" . DS . "Elements",
                        CORE . "View" . DS . "Elements"
                    )
                        ), $paths
        );
    }

    /**
     * Initializes the cache for App, registers a shutdown function.
     *
     * @return void
     */
    public static function init()
    {
        $loader = new ClassLoader(Config::read('App.namespace'), dirname(APP_PATH));
        $loader->register();

        register_shutdown_function(array(__CLASS__, 'shutdown'));
    }

    /**
     *  Retorna o caminho completo de um arquivo dentro da aplicação.
     *
     *  @param string $type Tipo do arquivo a ser buscado
     *  @param string $file Nome do arquivo a ser buscado
     *  @param string $ext Extensão do arquivo a ser buscado
     *  @return mixed Caminho completo do arquivo ou falso caso não exista
     */
    public static function path($type = "Core", $file = null, $ext = "php")
    {
        $parts = explode("/", $type);

        $originalPath = isset(self::$legacy[$parts[0]]) ? self::$legacy[$parts[0]] : $type;

        if (is_array($originalPath)) {
            $extra = self::extractTypesPaths($parts);

            foreach ($originalPath as $path) {
                if (!is_null($file)) {
                    $file_path = $path . $extra . DS . "{$file}.{$ext}";
                } else {
                    $file_path = $path . $extra . DS;
                }
                if (file_exists($file_path)) {
                    return $file_path;
                }
            }
        } else {
            if (!is_null($file)) {
                $file_path = CORE . $type . DS . "{$file}.{$ext}";
            } else {
                $file_path = CORE . $type . DS;
            }
            if (file_exists($file_path)) {
                return $file_path;
            }
        }
        return false;
    }

    private static function extractTypesPaths(Array $parts)
    {
        $extra = "";
        if (count($parts) > 1) {
            for ($i = 1; $i <= count($parts) - 1; $i++) {
                $extra .= DS . $parts[$i];
            }
        }
        return $extra;
    }

    /**
     * Object destructor.
     *
     * Writes cache file if changes have been made to the $_map
     *
     * @return void
     */
    public static function shutdown()
    {
        //static::checkFatalError();
    }

    /**
     * Return the classname namespaced. This method check if the class is defined on the
     * application/plugin, otherwise try to load from the CakePHP core
     *
     * @param string $class Classname
     * @param string $type Type of class
     * @param string $suffix Classname suffix
     * @return boolean|string False if the class is not found or namespaced classname
     */
    public static function classname($class, $type = '', $suffix = '')
    {
        if (strpos($class, '\\') !== false) {
            return $class;
        }

        $name = $class;

        $checkCore = true;

        $base = Config::read('App.namespace');

        $base = rtrim($base, '\\');

        if ($type === 'Lib') {
            $fullname = '\\' . $name . $suffix;
            if (class_exists($base . $fullname)) {
                return $base . $fullname;
            }
        }
        $fullname = '\\' . str_replace('/', '\\', $type) . '\\' . $name . $suffix;

        if (class_exists($base . $fullname)) {
            return $base . $fullname;
        }

        if ($checkCore) {
            if ($type === 'Lib') {
                $fullname = '\\' . $name . $suffix;
            }
            if (class_exists('Easy' . $fullname)) {
                return 'Easy' . $fullname;
            }
        }
        return false;
    }

    /**
     * Check if a fatal error happened and trigger the configured handler if configured
     *
     * @return void
     */
    protected static function checkFatalError()
    {
        $lastError = error_get_last();
        if (!is_array($lastError)) {
            return;
        }

        list(, $log) = Error::mapErrorCode($lastError['type']);
        if ($log !== LOG_ERR) {
            return;
        }

        if (PHP_SAPI === 'cli') {
            $errorHandler = Config::read('Error.consoleHandler');
        } else {
            $errorHandler = Config::read('Error.handler');
        }
        if (!is_callable($errorHandler)) {
            return;
        }
        call_user_func($errorHandler, $lastError['type'], $lastError['message'], $lastError['file'], $lastError['line'], array());
    }

}