<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel;

use Easy\HttpKernel\Bundle\BundleInterface;
use Easy\HttpKernel\DependencyInjection\AddClassesToCachePass;
use Easy\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionObject;
use RuntimeException;
use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Kernel implements KernelInterface, TerminableInterface
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
    protected $booted;
    protected $startTime;
    protected $name;

    const VERSION = '2.1.0-DEV';
    const VERSION_ID = '20100';
    const MAJOR_VERSION = '2';
    const MINOR_VERSION = '1';
    const RELEASE_VERSION = '0';
    const EXTRA_VERSION = 'DEV';

    public function __construct($environment, $debug)
    {
        $this->environment = $environment;
        $this->debug = (boolean) $debug;
        $this->booted = false;
        $this->rootDir = $this->getRootDir();
        $this->appDir = $this->getApplicationRootDir();
        $this->frameworkDir = $this->getFrameworkDir();
        $this->name = $this->getName();

        if ($this->debug) {
            $this->startTime = microtime(true);
        }
    }

    public function __clone()
    {
        if ($this->debug) {
            $this->startTime = microtime(true);
        }

        $this->booted = false;
        $this->container = null;
    }

    /**
     * Shutdowns the kernel.
     *
     * This method is mainly useful when doing functional testing.
     *
     * @api
     */
    public function shutdown()
    {
        if (false === $this->booted) {
            return;
        }

        $this->booted = false;

        foreach ($this->getBundles() as $bundle) {
            $bundle->shutdown();
            $bundle->setContainer(null);
        }

        $this->container = null;
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
     * Gets the name of the kernel
     *
     * @return string The kernel name
     *
     * @api
     */
    public function getName()
    {
        if (null === $this->name) {
            $this->name = preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->rootDir));
        }

        return $this->name;
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
    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        // init bundles
        $this->initializeBundles();

        //init container
        $this->initializeContainer();

        foreach ($this->getBundles() as $bundle) {
            $bundle->setContainer($this->container);
            $bundle->boot();
        }

        $this->booted = true;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->request = $request;

        if (false === $this->booted) {
            $this->boot();
        }

        return $this->getHttpKernel()->handle($request, $type, $catch);
    }

    /**
     * Returns the file path for a given resource.
     *
     * A Resource can be a file or a directory.
     *
     * The resource name must follow the following pattern:
     *
     *     @<BundleName>/path/to/a/file.something
     *
     * where BundleName is the name of the bundle
     * and the remaining part is the relative path in the bundle.
     *
     * If $dir is passed, and the first segment of the path is "Resources",
     * this method will look for a file named:
     *
     *     $dir/<BundleName>/path/without/Resources
     *
     * before looking in the bundle resource folder.
     *
     * @param string  $name  A resource name to locate
     * @param string  $dir   A directory where to look for the resource first
     * @param Boolean $first Whether to return the first path or paths for all matching bundles
     *
     * @return string|array The absolute path of the resource or an array if $first is false
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe
     * @throws \RuntimeException         if a custom resource is hidden by a resource in a derived bundle
     *
     * @api
     */
    public function locateResource($name, $dir = null, $first = true)
    {
        if ('@' !== $name[0]) {
            throw new \InvalidArgumentException(sprintf('A resource name must start with @ ("%s" given).', $name));
        }

        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }

        $bundleName = substr($name, 1);
        $path = '';
        if (false !== strpos($bundleName, '/')) {
            list($bundleName, $path) = explode('/', $bundleName, 2);
        }

        $isResource = 0 === strpos($path, 'Resources') && null !== $dir;
        $overridePath = substr($path, 9);
        $resourceBundle = null;
        $bundles = $this->getBundle($bundleName, false);
        $files = array();

        foreach ($bundles as $bundle) {
            if ($isResource && file_exists($file = $dir . '/' . $bundle->getName() . $overridePath)) {
                if (null !== $resourceBundle) {
                    throw new \RuntimeException(sprintf('"%s" resource is hidden by a resource from the "%s" derived bundle. Create a "%s" file to override the bundle resource.', $file, $resourceBundle, $dir . '/' . $bundles[0]->getName() . $overridePath
                    ));
                }

                if ($first) {
                    return $file;
                }
                $files[] = $file;
            }

            if (file_exists($file = $bundle->getPath() . '/' . $path)) {
                if ($first && !$isResource) {
                    return $file;
                }
                $files[] = $file;
                $resourceBundle = $bundle->getName();
            }
        }

        if (count($files) > 0) {
            return $first && $isResource ? $files[0] : $files;
        }

        throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return $this->name . ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }

    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     *
     * @return string
     */
    protected function getContainerBaseClass()
    {
        return 'Container';
    }

    /**
     * Gets the application root dir
     * @return string
     */
    public function getApplicationRootDir()
    {
        return $this->getRootDir() . "/app";
    }

    /**
     * Gets the package root dir
     * @return string
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $r = new ReflectionObject($this);
            $this->rootDir = dirname($this->getRecursiveDirname($r->getFileName(), 1));
        }
        return $this->rootDir;
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
        return $this->getApplicationRootDir() . "/config";
    }

    /**
     * Gets the temp dir
     * @return string
     */
    public function getTempDir()
    {
        return $this->getApplicationRootDir() . "/tmp";
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
     * Initializes the service container.
     *
     * The cached version of the service container is used when fresh, otherwise the
     * container is built.
     */
    public function initializeContainer()
    {
        $this->container = $this->buildContainer();
        $this->container->set('kernel', $this);
        $this->container->set('request', $this->request);
        $this->container->compile();
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        $container = new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
        return $container;
    }

    /**
     * Returns the kernel parameters.
     *
     * @return array An array of kernel parameters
     */
    protected function getKernelParameters()
    {
        $bundles = array();
        foreach ($this->bundles as $name => $bundle) {
            $bundles[$name] = get_class($bundle);
        }

        return array_merge(
                array(
            'kernel.root_dir' => $this->rootDir,
            'kernel.environment' => $this->environment,
            'kernel.debug' => $this->debug,
            'kernel.name' => $this->name,
            'kernel.cache_dir' => $this->getCacheDir(),
            'kernel.logs_dir' => $this->getLogDir(),
            'kernel.bundles' => $bundles,
            'kernel.container_class' => $this->getContainerClass(),
                ), $this->getEnvParameters(), $this->getServerParameters()
        );
    }

    /**
     * Gets the server parameters.
     *
     * @return array An array of parameters
     */
    protected function getServerParameters()
    {
        $fn = function() {
                    if (isset($_SERVER["SERVER_PROTOCOL"])) {
                        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https://' ? 'https://' : 'http://';

                        $path = $_SERVER['PHP_SELF'];

                        $path_parts = pathinfo($path);
                        $directory = $path_parts['dirname'];

                        $directory = ($directory == "/") ? "" : $directory;

                        $host = $_SERVER['HTTP_HOST'];

                        return $protocol . $host . $directory;
                    }
                };

        $parameters = array();

        $parameters['server.base_url'] = $fn();
        return $parameters;
    }

    /**
     * Gets the environment parameters.
     *
     * Only the parameters starting with "EASY__" are considered.
     *
     * @return array An array of parameters
     */
    protected function getEnvParameters()
    {
        $parameters = array();
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'EASY__')) {
                $parameters[strtolower(str_replace('__', '.', substr($key, 9)))] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Builds the service container.
     *
     * @return ContainerBuilder The compiled service container
     */
    protected function buildContainer()
    {
        foreach (array('cache' => $this->getCacheDir(), 'logs' => $this->getLogDir()) as $name => $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true)) {
                    throw new RuntimeException(sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
            }
        }

        $container = $this->getContainerBuilder();
        $container->set("service_container", $container);

        $container->addObjectResource($this);
        $this->prepareContainer($container);

        if (null !== $cont = $this->registerContainerConfiguration($this->getContainerLoader($container))) {
            $container->merge($cont);
        }

        $container->addCompilerPass(new AddClassesToCachePass($this));

        return $container;
    }

    /**
     * Prepares the ContainerBuilder before it is compiled.
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
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

        // ensure these extensions are implicitly loaded
        $container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions));
    }

    /**
     * Loads the PHP class cache.
     *
     * @param string $name      The cache name prefix
     * @pa ram string $extension File extension of the resulting file
     */
    public function loadClassCache($name = 'classes', $extension = '.php')
    {
        if (!$this->booted && is_file($this->getCacheDir() . '/classes.map')) {
            ClassCollectionLoader::load(include($this->getCacheDir() . '/classes.map'), $this->getCacheDir(), $name, $this->debug, false, $extension);
        }
    }

    /**
     * Used internally.
     */
    public function setClassCache(array $classes)
    {
        file_put_contents($this->getCacheDir() . '/classes.map', sprintf('<?php return %s;', var_export($classes, true)));
    }

    /**
     * Returns a loader for the container.
     *
     * @param ContainerInterface $container The service container
     *
     * @return DelegatingLoader The loader
     */
    protected function getContainerLoader(ContainerInterface $container)
    {
        $locator = new FileLocator($this);
        $resolver = new LoaderResolver(array(
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new ClosureLoader($container),
        ));

        return new DelegatingLoader($resolver);
    }

    public function terminate(Request $request, Response $response)
    {
        if ($this->container) {
            if ($this->getHttpKernel() instanceof TerminableInterface) {
                $this->getHttpKernel()->terminate($request, $response);
            }
        }
    }

    /**
     * Gets a http kernel from the container
     *
     * @return HttpKernel
     */
    protected function getHttpKernel()
    {
        return $this->container->get("http_kernel");
    }

    /**
     * Gets the current container.
     *
     * @return ContainerBuilder A ContainerInterface instance
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param ConfigCache      $cache     The config cache
     * @param ContainerBuilder $container The service container
     * @param string           $class     The name of the class to generate
     * @param string           $baseClass The name of the container's base class
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
// cache the container
        $dumper = new PhpDumper($container);
        $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));

        if (!$this->debug) {
            $content = self::stripComments($content);
        }

        $cache->write($content, $container->getResources());
    }

    /**
     * Removes comments from a PHP source string.
     *
     * We don't use the PHP php_strip_whitespace() function
     * as we want the content to be readable and well-formatted.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the comments removed
     */
    public static function stripComments($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $rawChunk = '';
        $output = '';
        $tokens = token_get_all($source);
        for (reset($tokens); false !== $token = current($tokens); next($tokens)) {
            if (is_string($token)) {
                $rawChunk .= $token;
            } elseif (T_START_HEREDOC === $token[0]) {
                $output .= preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $rawChunk) . $token[1];
                do {
                    $token = next($tokens);
                    $output .= $token[1];
                } while ($token[0] !== T_END_HEREDOC);
                $rawChunk = '';
            } elseif (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $rawChunk .= $token[1];
            }
        }

        // replace multiple new lines with a single newline
        $output .= preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $rawChunk);

        return $output;
    }

}

