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
$easyLib = "../src/Easy";
/**
 * Use the DS to separate the directories in other defines
 */
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
/**
 * Defines the framework installation path.
 */
defined('CORE') || define('CORE', $easyLib . DS);
/**
 * Defines the framework installation path.
 */
defined('EASY_ROOT') || define('EASY_ROOT', dirname(dirname($easyLib)) . DS);
/**
 * Path to the temporary files directory.
 */
defined('TMP') || define('TMP', 'tmp' . DS);
/**
 * Path to the cache files directory. It can be shared between hosts in a multi-server setup.
 */
defined('CACHE') || define('CACHE', TMP . 'cache' . DS);
/**
 * Path to the log files directory. It can be shared between hosts in a multi-server setup.
 */
defined('LOGS') || define('LOGS', TMP . 'logs' . DS);

if (!defined('LIB_PATH')) {
    define('LIB_PATH', dirname($easyLib));
}

/* Basic classes */
require CORE . 'basics.php';
require CORE . 'Core' . DS . 'ClassLoader.php';

defined('APP_PATH') || define('APP_PATH', dirname(__FILE__) . DS . 'EasyTest/App');

$loader = new Easy\Core\ClassLoader('EasyTest', dirname(__FILE__));
$loader->register();