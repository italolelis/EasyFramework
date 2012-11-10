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
use Easy\Mvc\Routing\Mapper;
use Easy\Utility\Hash;

/**
 * Connects the default, built-in routes. The following routes are created in the order below:
 * 
 * - `/:controller'
 * - `/:controller/:action/*'
 *
 * You can disable the connection of default routes by deleting the require inside APP/Config/routes.yaml.
 */
//loads user config routingn configuration
$connects = Config::read('Routing.connect');
if (!empty($connects)) {
    foreach ($connects as $url => $route) {
        $options = Hash::arrayUnset($route, 'options');
        Mapper::connect($url, $route, $options);
    }
}

$mapResources = Config::read('Routing.mapResources');
if (!empty($mapResources)) {
    foreach ($mapResources as $resource => $options) {
        if (is_array($options)) {
            foreach ($options as $k => $v) {
                $resource = $k;
                $options = $v;
            }
        } else {
            $resource = $options;
            $options = array();
        }
        Mapper::mapResources($resource, $options);
    }
}

$parseExtensions = Config::read('Routing.parseExtensions');
if (!empty($parseExtensions)) {
    Mapper::parseExtensions($parseExtensions);
}

$prefixes = Mapper::prefixes();

foreach ($prefixes as $prefix) {
    $params = array('prefix' => $prefix);
    $indexParams = $params + array('action' => 'index');
    Mapper::connect("/{$prefix}/:controller", $indexParams);
    Mapper::connect("/{$prefix}/:controller/:action/*", $params);
}
Mapper::connect('/:controller', array('action' => 'index'));
Mapper::connect('/:controller/:action/*');

unset($params, $indexParams, $prefix, $prefixes);