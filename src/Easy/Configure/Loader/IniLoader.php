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

namespace Easy\Configure\Loader;

use Symfony\Component\Config\Loader\FileLoader;

/**
 * Handles Yml config files
 */
class IniLoader extends FileLoader
{

    public function load($resource, $type = null)
    {
        $filename = $resource;
        if (!file_exists($filename)) {
            $filename .= '.ini';
            if (!file_exists($filename)) {
                throw new ConfigureException(__('Could not load configuration files: %s or %s', substr($filename, 0, -4), $filename));
            }
        }
        $contents = parse_ini_file($filename, true);
        if (!empty($this->_section) && isset($contents[$this->_section])) {
            $values = $this->_parseNestedValues($contents[$this->_section]);
        } else {
            $values = array();
            foreach ($contents as $section => $attribs) {
                if (is_array($attribs)) {
                    $values[$section] = $this->_parseNestedValues($attribs);
                } else {
                    $parse = $this->_parseNestedValues(array($attribs));
                    $values[$section] = array_shift($parse);
                }
            }
        }
        return $values;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo(
                        $resource, PATHINFO_EXTENSION
        );
    }

    /**
     * parses nested values out of keys.
     *
     * @param array $values Values to be exploded.
     * @return array Array of values exploded
     */
    protected function _parseNestedValues($values)
    {
        foreach ($values as $key => $value) {
            if ($value === '1') {
                $value = true;
            }
            if ($value === '') {
                $value = false;
            }

            if (strpos($key, '.') !== false) {
                $values = Set::insert($values, $key, $value);
                //$this->_parseNestedValues($values);
            } else {
                $values[$key] = $value;
            }
        }
        return $values;
    }

}
