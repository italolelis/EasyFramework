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
// PHPUnit doesn't understand relative paths well when they are in the config file.
chdir(__DIR__);

$phpunit_bin = __DIR__ . '/../vendor/bin/phpunit';
$phpunit_bin = file_exists($phpunit_bin) ? $phpunit_bin : 'phpunit';
$phpunit_conf = (file_exists('phpunit.xml') ? 'phpunit.xml' : 'phpunit.xml.dist');
$phpunit_opts = "-c $phpunit_conf";
$phpunit_coverage = '';


$run_as = 'paths';
$components = array();
$components = getAll($phpunit_conf);

//var_dump($components);

$result = 0;
if ($run_as == 'groups') {
    $groups = join(',', $components);
    echo "$groups:\n";
    system("$phpunit_bin $phpunit_opts $phpunit_coverage --group " . $groups, $result);
    echo "\n\n";
} else {
    foreach ($components as $component) {
        $component = 'EasyTest/' . basename(str_replace('_', '/', $component));
        echo "$component:\n";
        system("$phpunit_bin $phpunit_opts $phpunit_coverage " . escapeshellarg(__DIR__ . '/' . $component), $c_result);
        echo "\n\n";
        if ($c_result) {
            $result = $c_result;
        }
    }
}

exit($result);

// Functions
function getAll($phpunit_conf)
{
    $components = array();
    $conf = simplexml_load_file($phpunit_conf);
    $excludes = $conf->xpath('/phpunit/testsuites/testsuite/exclude/text()');
    for ($i = 0; $i < count($excludes); $i++) {
        $excludes[$i] = basename($excludes[$i]);
    }
    if ($handle = opendir(__DIR__ . '/EasyTest/')) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..' && !in_array($entry, $excludes)) {
                $components[] = $entry;
            }
        }
        closedir($handle);
    }
    sort($components);
    return $components;
}