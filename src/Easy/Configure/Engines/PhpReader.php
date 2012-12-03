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

namespace Easy\Configure\Engines;

use Easy\Configure\IConfigReader;
use Easy\Error\ConfigureException;

/**
 * PHP Reader allows Configure to load configuration values from
 * files containing simple PHP arrays.
 *
 * Files compatible with PhpReader should define a `$config` variable, that
 * contains all of the configuration data contained in the file.
 *
 * @package       Easy.Configure.Engines
 */
class PhpReader implements IConfigReader
{

    /**
     * The path this reader finds files on.
     *
     * @var string
     */
    protected $_path = null;

    /**
     * Constructor for PHP Config file reading.
     *
     * @param string $path The path to read config files from.  Defaults to APP . 'Config' . DS
     */
    public function __construct($path = null)
    {
        if (!$path) {
            $path = APP_PATH . 'Config' . DS;
        }
        $this->_path = $path;
    }

    /**
     * Read a config file and return its contents.
     *
     * Files with `.` in the name will be treated as values in plugins.  Instead of reading from
     * the initialized path, plugin keys will be located using App::pluginPath().
     *
     * @param string $key The identifier to read from.  If the key has a . it will be treated
     *  as a plugin prefix.
     * @return array Parsed configuration values.
     * @throws ConfigureException when files don't exist or they don't contain `$config`.
     *  Or when files contain '..' as this could lead to abusive reads.
     */
    public function read($key)
    {
        if (strpos($key, '..') !== false) {
            throw new ConfigureException(__('Cannot load configuration files with ../ in them.'));
        }
        if (substr($key, -4) === '.php') {
            $key = substr($key, 0, -4);
        }

        $file = $this->_path . $key;

        $file .= '.php';
        if (!is_file($file)) {
            if (!is_file(substr($file, 0, -4))) {
                throw new ConfigureException(__('Could not load configuration files: %s or %s', $file, substr($file, 0, -4)));
            }
        }
        $config = file_get_contents($file);
        return $config;
    }

}
