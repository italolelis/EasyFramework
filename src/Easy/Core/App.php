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

/**
 * App is responsible for path management, class location and class loading.
 * 
 * @since 0.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class App
{

    /**
     * Is the Application on debug mode?
     * @var bool
     */
    public static function isDebug()
    {
        return Config::read('App.debug');
    }

    /**
     * Obtêm a versão do core
     * @return string 
     */
    public static function getVersion()
    {
        return "2.0.0-rc";
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

}
