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

namespace Easy\HttpKernel;

use Doctrine\Common\Cache\FilesystemCache;
use Easy\Collections\Dictionary;
use Easy\Configure\IConfiguration;
use Easy\Configure\Loader\IniLoader;
use Easy\Configure\Loader\PhpLoader;
use Easy\Configure\Loader\XmlLoader;
use Easy\Configure\Loader\YamlLoader;
use Easy\Core\Config;
use Easy\Error\ErrorHandler;
use Easy\Error\ExceptionHandler;
use Easy\HttpKernel\Bundle\Bundle;
use Easy\Network\Request;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionObject;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

abstract class Kernel implements KernelInterface, IConfiguration
{

    /**
     * @var array 
     */
    protected $bundles;

    /**
     * @var array 
     */
    protected $bundleMap;

    /**
     * @var string 
     */
    public $engine = 'yaml';

    /**
     * @var Dictionary 
     */
    protected $configs;

    /**
     * @var string 
     */
    protected $environment;

    /**
     * @var boolean 
     */
    protected $debug;

    /**
     * @var int 
     */
    protected $errorReportingLevel;

    /**
     * @var string 
     */
    protected $rootDir;

    /**
     * @var string 
     */
    protected $appDir;

    /**
     * @var string 
     */
    protected $frameworkDir;

    /**
     * @var Request 
     */
    protected $request;

    /**
     * @var ContainerBuilder $container
     */
    protected $container = null;

    const VERSION = '2.0.0';
    const VERSION_ID = '20000';
    const MAJOR_VERSION = '2';
    const MINOR_VERSION = '0';
    const RELEASE_VERSION = '0';
    const EXTRA_VERSION = '';

    public function __construct($environment, $debug)
    {
        $this->environment = $environment;
        $this->debug = (boolean) $debug;
        $this->rootDir = $this->getRootDir();
        $this->appDir = $this->getApplicationRootDir();
        $this->frameworkDir = $this->getFrameworkDir();
        $this->boot();
    }

    /**
     * Gets the configuration engine
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Sets the configuration engine
     * @param string $engine
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * Gets an value from configs based on provided key. You can use namespaced config keys like
     * <code>
     * $config->get(namespace.foo);
     * $config->get(namespace.bar);
     * $config->get(namespace);
     * </code>
     * @param string $value
     * @return null
     */
    public function get($value)
    {
        $pointer = $this->configs->GetArray();
        foreach (explode('.', $value) as $key) {
            if (isset($pointer[$key])) {
                $pointer = $pointer[$key];
            } else {
                return null;
            }
        }
        return $pointer;
    }

    /**
     * Gets the application environment
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Check if the application is in debug mode
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Configure default application configurations
     * @param string $engine
     */
    private function boot()
    {
        if ($this->debug) {
            ini_set('display_errors', 1);
            error_reporting(-1);

            ErrorHandler::register($this->errorReportingLevel);
            if ('cli' !== php_sapi_name()) {
                ExceptionHandler::register();
            }
        } else {
            ini_set('display_errors', 0);
        }

        // init bundles
        $this->initializeBundles();
        // init container
        $this->initializeContainer();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request)
    {
        if ($this->request === null) {
            $this->request = $request;
            $this->configs = new Dictionary($this->loadConfigFiles($this->getLoader(), $this->engine));
        }
        $httpKernel = new HttpKernel($this);
        return $httpKernel->handle($this->request);
    }

    public function loadConfigFiles(LoaderInterface $loader, $type = null)
    {
        $configs = false;
        $cache = new FilesystemCache($this->getCacheDir());

        if ($this->isDebug()) {
            $configs = $cache->fetch("configs");
        }

        if (!$configs) {
            $configs = $loader->load($this->getConfigDir() . "/application." . $type);
            $bundleConfigs = $this->getActiveBundle()->loadConfigurations($loader, $type);

            $configs = array_merge($configs, $bundleConfigs);
            $cache->save("configs", $configs);
        }

        Config::write($configs);
        return Config::read();
    }

    /**
     * Gets the application root dir
     * @return string
     */
    public function getApplicationRootDir()
    {
        return $this->rootDir . "/app";
    }

    /**
     * Gets the package root dir
     * @return string
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $r = new ReflectionObject($this);
            $this->rootDir = $this->getRecursiveDirname($r->getFileName(), 1);
        }
        return dirname($this->rootDir);
    }

    /**
     * Gets the framework root dir
     * @return string
     */
    public function getFrameworkDir()
    {
        if (null === $this->frameworkDir) {
            $r = new ReflectionClass(get_parent_class($this));
            $this->frameworkDir = $this->getRecursiveDirname($r->getFileName(), 2);
        }

        return $this->frameworkDir;
    }

    /**
     * Gets the temp dir
     * @return string
     */
    public function getConfigDir()
    {
        return $this->appDir . "/config";
    }

    /**
     * Gets the temp dir
     * @return string
     */
    public function getTempDir()
    {
        return $this->rootDir . "/tmp";
    }

    /**
     * Gets the cache dir
     * @return string
     */
    public function getCacheDir()
    {
        return $this->getTempDir() . '/cache';
    }

    /**
     * Gets the logs dir
     * @return string
     */
    public function getLogDir()
    {
        return $this->getTempDir() . '/logs';
    }

    /**
     * Recursivly gets the dirname of a directory
     * @param string $dir The dir name
     * @param integer $deep The deep of recursive search
     * @param integer $current The current deepth of recursive search
     * @return string
     */
    protected function getRecursiveDirname($dir, $deep, $current = 0)
    {
        if ($deep !== $current) {
            return $this->getRecursiveDirname(dirname($dir), $deep, $current + 1);
        }
        return str_replace('\\', '/', $dir);
    }

    /**
     * Initializes the data structures related to the bundle management.
     *
     *  - the bundles property maps a bundle name to the bundle instance,
     *  - the bundleMap property maps a bundle name to the bundle inheritance hierarchy (most derived bundle first).
     *
     * @throws LogicException if two bundles share a common name
     * @throws LogicException if a bundle tries to extend a non-registered bundle
     * @throws LogicException if a bundle tries to extend itself
     * @throws LogicException if two bundles extend the same ancestor
     */
    protected function initializeBundles()
    {
        // init bundles
        $this->bundles = array();
        $topMostBundles = array();
        $directChildren = array();

        foreach ($this->registerBundles() as $bundle) {
            $name = $bundle->getName();
            if (isset($this->bundles[$name])) {
                throw new LogicException(sprintf('Trying to register two bundles with the same name "%s"', $name));
            }
            $this->bundles[$name] = $bundle;

            if ($parentName = $bundle->getParent()) {
                if (isset($directChildren[$parentName])) {
                    throw new LogicException(sprintf('Bundle "%s" is directly extended by two bundles "%s" and "%s".', $parentName, $name, $directChildren[$parentName]));
                }
                if ($parentName == $name) {
                    throw new LogicException(sprintf('Bundle "%s" can not extend itself.', $name));
                }
                $directChildren[$parentName] = $name;
            } else {
                $topMostBundles[$name] = $bundle;
            }
        }

        // look for orphans
        if (count($diff = array_values(array_diff(array_keys($directChildren), array_keys($this->bundles))))) {
            throw new LogicException(sprintf('Bundle "%s" extends bundle "%s", which is not registered.', $directChildren[$diff[0]], $diff[0]));
        }

        // inheritance
        $this->bundleMap = array();
        foreach ($topMostBundles as $name => $bundle) {
            $bundleMap = array($bundle);
            $hierarchy = array($name);

            while (isset($directChildren[$name])) {
                $name = $directChildren[$name];
                array_unshift($bundleMap, $this->bundles[$name]);
                $hierarchy[] = $name;
            }

            foreach ($hierarchy as $bundle) {
                $this->bundleMap[$bundle] = $bundleMap;
                array_pop($bundleMap);
            }
        }
    }

    /**
     * Gets the registered bundle instances.
     *
     * @return array An array of registered bundle instances
     *
     * @api
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * Checks if a given class name belongs to an active bundle.
     *
     * @param string $class A class name
     *
     * @return Boolean true if the class belongs to an active bundle, false otherwise
     *
     * @api
     */
    public function isClassInActiveBundle($class)
    {
        foreach ($this->getBundles() as $bundle) {
            if (0 === strpos($class, $bundle->getNamespace())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a bundle and optionally its descendants by its name.
     *
     * @param string  $name  Bundle name
     * @param Boolean $first Whether to return the first bundle only or together with its descendants
     *
     * @return BundleInterface|Array A BundleInterface instance or an array of BundleInterface instances if $first is false
     *
     * @throws InvalidArgumentException when the bundle is not enabled
     *
     * @api
     */
    public function getBundle($name, $first = true)
    {
        if (!isset($this->bundleMap[$name])) {
            throw new InvalidArgumentException(sprintf('Bundle "%s" does not exist or it is not enabled. Maybe you forgot to add it in the registerBundles() method of your %s.php file?', $name, get_class($this)));
        }

        if (true === $first) {
            return $this->bundleMap[$name][0];
        }

        return $this->bundleMap[$name];
    }

    /**
     * Gets the active bundle, based on the request prefix
     * @return Bundle
     */
    public function getActiveBundle()
    {
        $bundleName = "AppBundle";
        if ($this->request->prefix !== null) {
            $bundleName = ucfirst($this->request->prefix) . "Bundle";
        }
        return $this->getBundle($bundleName);
    }

    /**
     * Returns a loader for the container.
     *
     * @return DelegatingLoader The loader
     */
    protected function getLoader()
    {
        $locator = new FileLocator($this);
        $resolver = new LoaderResolver(array(
            new XmlLoader($locator),
            new YamlLoader($locator),
            new IniLoader($locator),
            new PhpLoader($locator),
        ));

        return new DelegatingLoader($resolver);
    }

    /**
     * Initializes the service container.
     *
     * The cached version of the service container is used when fresh, otherwise the
     * container is built.
     */
    protected function initializeContainer()
    {
        $container = $this->buildContainer();
        $this->container = $container;
        $this->container->set('kernel', $this);
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        return new ContainerBuilder();
    }

    /**
     * Builds the service container.
     *
     * @return ContainerBuilder The compiled service container
     */
    protected function buildContainer()
    {

        $container = $this->getContainerBuilder();

        $extensions = array();
        foreach ($this->bundles as $bundle) {
            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
                $extensions[] = $extension->getAlias();
            }

            if ($this->debug) {
                $container->addObjectResource($bundle);
            }
        }
        foreach ($this->bundles as $bundle) {
            $bundle->build($container);
        }

        $container->addObjectResource($this);
        //$container->compile();

        return $container;
    }

    /**
     * Gets the current container.
     *
     * @return ContainerInterface A ContainerInterface instance
     */
    public function getContainer()
    {
        return $this->container;
    }

}
