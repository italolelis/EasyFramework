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

namespace Easy\Mvc\Routing\Filter;

use Easy\Core\App;
use Easy\Core\Config;
use Easy\Core\Plugin;
use Easy\Event\Event;
use Easy\Network\Response;
use Easy\Mvc\Routing\DispatcherFilter;
use Easy\Utility\Inflector;

/**
 * Filters a request and tests whether it is a file in the webroot folder or not and
 * serves the file to the client if appropriate.
 */
class AssetDispatcher extends DispatcherFilter
{

    /**
     * Default priority for all methods in this filter
     * This filter should run before the request gets parsed by router
     *
     * @var int
     * */
    public $priority = 9;

    /**
     * Checks if a requested asset exists and sends it to the browser
     *
     * @param Event $event containing the request and response object
     * @return Response if the client is requesting a recognized asset, null otherwise
     */
    public function beforeDispatch($event)
    {
        $url = $event->data['request']->url;
        if (strpos($url, '..') !== false || strpos($url, '.') === false) {
            return;
        }

        $assetFile = $this->_getAssetFile($url);
        if ($assetFile === null || !file_exists($assetFile)) {
            return null;
        }

        $response = $event->data['response'];
        $event->stopPropagation();

        $response->modified(filemtime($assetFile));
        if ($response->checkNotModified($event->data['request'])) {
            return $response;
        }

        $pathSegments = explode('.', $url);
        $ext = array_pop($pathSegments);
        $this->_deliverAsset($response, $assetFile, $ext);
        return $response;
    }

    /**
     * Builds asset file path based off url
     *
     * @param string $url
     * @return string Absolute path for asset file
     */
    protected function _getAssetFile($url)
    {
        $parts = explode('/', $url);
        if ($parts[0] === 'theme') {
            $themeName = $parts[1];
            unset($parts[0], $parts[1]);
            $fileFragment = urldecode(implode(DS, $parts));
            $path = App::themePath($themeName) . 'public' . DS;
            return $path . $fileFragment;
        }

        $plugin = Inflector::camelize($parts[0]);
        if (Plugin::loaded($plugin)) {
            unset($parts[0]);
            $fileFragment = urldecode(implode(DS, $parts));
            $pluginWebroot = Plugin::path($plugin) . 'public' . DS;
            return $pluginWebroot . $fileFragment;
        }
    }

    /**
     * Sends an asset file to the client
     *
     * @param Response $response The response object to use.
     * @param string $assetFile Path to the asset file in the file system
     * @param string $ext The extension of the file to determine its mime type
     * @return void
     */
    protected function _deliverAsset(Response $response, $assetFile, $ext)
    {
        ob_start();
        $compressionEnabled = Config::read('Asset.compress') && $response->compress();
        if ($response->type($ext) == $ext) {
            $contentType = 'application/octet-stream';
            $agent = env('HTTP_USER_AGENT');
            if (preg_match('%Opera(/| )([0-9].[0-9]{1,2})%', $agent) || preg_match('/MSIE ([0-9].[0-9]{1,2})/', $agent)) {
                $contentType = 'application/octetstream';
            }
            $response->type($contentType);
        }
        if (!$compressionEnabled) {
            $response->header('Content-Length', filesize($assetFile));
        }
        $response->cache(filemtime($assetFile));
        $response->send();
        ob_clean();
        readfile($assetFile);
        if ($compressionEnabled) {
            ob_end_flush();
        }
    }

}