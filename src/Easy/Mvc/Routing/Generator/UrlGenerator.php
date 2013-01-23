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

namespace Easy\Mvc\Routing\Generator;

use Easy\Mvc\Routing\Mapper;
use Easy\Network\Request;

/**
 * UrlGenerator can generate a URL or a path for any route in the RouteCollection
 * based on the passed parameters.
 *
 * @since 1.7
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class UrlGenerator implements IUrlGenerator
{

    /**
     * @var string 
     */
    protected $prefix;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string 
     */
    protected $controllerName;

    public function __construct(Request $request, $controllerName)
    {
        $this->request = $request;
        $this->controllerName = $controllerName;
        $this->prefix = strtolower($this->request->prefix);
    }

    public function doCreate($path, $referenceType = self::ABSOLUTE_URL)
    {
        if ($referenceType === self::RELATIVE_PATH) {
            $referenceType = false;
            $url = static::getRelativePath(Mapper::url(), $path);
        } elseif ($referenceType === self::NETWORK_PATH) {
            $url = "//" . Mapper::url($path, $referenceType);
        } else {
            $url = Mapper::url($path, $referenceType);
        }
        return $url;
    }

    /**
     * Converts a virtual (relative) path to an application absolute path.
     * @param string $string The path to convert
     * @return string An absolute url to the path
     */
    public function content($path, $referenceType = self::ABSOLUTE_URL)
    {
        $options = array();
        if (is_array($path)) {
            return $this->doCreate($path, $referenceType);
        }

        if (strpos($path, '://') === false) {
            if (!empty($options['pathPrefix']) && $path[0] !== '/') {
                $path = $options['pathPrefix'] . $path;
            }
            if (
                    !empty($options['ext']) &&
                    strpos($path, '?') === false &&
                    substr($path, -strlen($options['ext'])) !== $options['ext']
            ) {
                $path .= $options['ext'];
            }

            if ($referenceType === self::ABSOLUTE_URL) {
                $base = $this->doCreate("/", true);
                $len = strlen($this->request["webroot"]);
                if ($len) {
                    $base = substr($base, 0, -$len);
                }
                $path = $base . $path;
            }
        }
        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function create($actionName, $controllerName = null, $params = null, $area = true, $referenceType = self::ABSOLUTE_URL)
    {
        if ($controllerName === true) {
            list(, $controllerName) = namespaceSplit($this->controllerName);
        }

        $url = array(
            'controller' => strtolower(urlencode($controllerName)),
            'action' => $actionName,
            $params
        );

        if ($area === true) {
            if ($this->prefix) {
                $url["prefix"] = $this->prefix;
            }
        } elseif (is_string($area)) {
            $url["prefix"] = $area;
        }

        return $this->doCreate($url, $referenceType);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getBase($referenceType = self::ABSOLUTE_URL)
    {
        return $this->doCreate("/", $referenceType);
    }

    /**
     * Gets the base url to your application
     * @return string The base url to your application 
     */
    public function getAreaBase($referenceType = self::ABSOLUTE_URL)
    {
        $area = null;
        if ($this->prefix) {
            $area = "/" . strtolower($this->prefix);
        }
        return $this->getBase($referenceType) . $area;
    }

    /**
     * Returns the target path as relative reference from the base path.
     *
     * Only the URIs path component (no schema, host etc.) is relevant and must be given, starting with a slash.
     * Both paths must be absolute and not contain relative parts.
     * Relative URLs from one resource to another are useful when generating self-contained downloadable document archives.
     * Furthermore, they can be used to reduce the link size in documents.
     *
     * Example target paths, given a base path of "/a/b/c/d":
     * - "/a/b/c/d"     -> ""
     * - "/a/b/c/"      -> "./"
     * - "/a/b/"        -> "../"
     * - "/a/b/c/other" -> "other"
     * - "/a/x/y"       -> "../../x/y"
     *
     * @param string $basePath   The base path
     * @param string $targetPath The target path
     *
     * @return string The relative target path
     */
    public static function getRelativePath($basePath, $targetPath)
    {
        if ($basePath === $targetPath) {
            return '';
        }

        $sourceDirs = explode('/', isset($basePath[0]) && '/' === $basePath[0] ? substr($basePath, 1) : $basePath);
        $targetDirs = explode('/', isset($targetPath[0]) && '/' === $targetPath[0] ? substr($targetPath, 1) : $targetPath);
        array_pop($sourceDirs);
        $targetFile = array_pop($targetDirs);

        foreach ($sourceDirs as $i => $dir) {
            if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
                unset($sourceDirs[$i], $targetDirs[$i]);
            } else {
                break;
            }
        }

        $targetDirs[] = $targetFile;
        $path = str_repeat('../', count($sourceDirs)) . implode('/', $targetDirs);

        // A reference to the same base directory or an empty subdirectory must be prefixed with "./".
        // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
        // as the first segment of a relative-path reference, as it would be mistaken for a scheme name
        // (see http://tools.ietf.org/html/rfc3986#section-4.2).
        return '' === $path || '/' === $path[0] || false !== ($colonPos = strpos($path, ':')) && ($colonPos < ($slashPos = strpos($path, '/')) || false === $slashPos) ? "./$path" : $path;
    }

}