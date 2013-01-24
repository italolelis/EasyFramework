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

use Easy\Core\Config;
use Easy\Localization\I18n;
use Symfony\Component\Locale\Locale;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Recursively strips slashes from all values in an array
 *
 * @param array $values Array of values to strip slashes
 * @return mixed What is returned from calling stripslashes
 */
function stripslashes_deep($values)
{
    if (is_array($values)) {
        foreach ($values as $key => $value) {
            $values[$key] = stripslashes_deep($value);
        }
    } else {
        $values = stripslashes($values);
    }
    return $values;
}

/**
 * Print_r convenience function, which prints out <PRE> tags around
 * the output of given array. Similar to debug().
 *
 * @param array $var Variable to print out
 */
function pr($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * Returns a translated string if one is found; Otherwise, the submitted message.
 *
 * @param string $value Text to translate
 * @param mixed $args Array with arguments or multiple arguments in function
 * @return mixed translated string
 */
function __($value, $args = null)
{
    if (!$value) {
        return;
    }
    $translated = I18n::translate($value);
    if ($args === null) {
        return $translated;
    } elseif (!is_array($args)) {
        $args = array_slice(func_get_args(), 1);
    }
    return vsprintf($translated, $args);
}

function configTranslator()
{
    $locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $translator = new Translator($locale);
    $translator->setFallbackLocale(Config::read('Components.Translator.default_locale'));
    $translator->addLoader('pofile', new PoFileLoader());
    $iterator = new FilesystemIterator(APP_PATH . "Locale/LC_MESSAGES");
    $filter = new RegexIterator($iterator, '/\.(po)$/');
    foreach ($filter as $entry) {
        $translator->addResource('pofile', $entry->getPathname(), $translator->getLocale());
    }

    return $translator;
}

/**
 * Allows you to override the current domain for a single message lookup.
 *
 * @param string $domain Domain
 * @param string $msg String to translate
 * @param mixed $args Array with arguments or multiple arguments in function
 * @return translated string
 */
function __d($domain, $msg, $args = null)
{
    if (!$msg) {
        return;
    }
    $translated = I18n::translate($msg, null, $domain);
    if ($args === null) {
        return $translated;
    } elseif (!is_array($args)) {
        $args = array_slice(func_get_args(), 2);
    }
    return vsprintf($translated, $args);
}

/**
 * Split the namespace from the classname.
 *
 * Commonly used like `list($namespace, $classname) = namespaceSplit($class);`
 *
 * @param string $class The full class name, ie `Cake\Core\App`
 * @return array Array with 2 indexes. 0 => namespace, 1 => classname
 */
function namespaceSplit($class)
{
    $pos = strrpos($class, '\\');
    if ($pos === false) {
        return array('', $class);
    }
    return array(substr($class, 0, $pos), substr($class, $pos + 1));
}

if (!function_exists('h')) {

    /**
     * Convenience method for htmlspecialchars.
     *
     * @param string|array|object $text Text to wrap through htmlspecialchars.  Also works with arrays, and objects.
     *    Arrays will be mapped and have all their elements escaped.  Objects will be string cast if they
     *    implement a `__toString` method.  Otherwise the class name will be used.
     * @param boolean $double Encode existing html entities
     * @param string $charset Character set to use when escaping.  Defaults to config value in 'App.encoding' or 'UTF-8'
     * @return string Wrapped text
     */
    function h($text, $double = true, $charset = null)
    {
        if (is_array($text)) {
            $texts = array();
            foreach ($text as $k => $t) {
                $texts[$k] = h($t, $double, $charset);
            }
            return $texts;
        } elseif (is_object($text)) {
            if (method_exists($text, '__toString')) {
                $text = (string) $text;
            } else {
                $text = '(object)' . get_class($text);
            }
        } elseif (is_bool($text)) {
            return $text;
        }

        static $defaultCharset = false;
        if ($defaultCharset === false) {
            $defaultCharset = Config::read('App.encoding');
            if ($defaultCharset === null) {
                $defaultCharset = 'UTF-8';
            }
        }
        if (is_string($double)) {
            $charset = $double;
        }
        return htmlspecialchars($text, ENT_QUOTES, ($charset) ? $charset : $defaultCharset, $double);
    }

}
