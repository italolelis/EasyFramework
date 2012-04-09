<?php

/**
 *  App cuida de tarefas relativas a importação de arquivos dentro de uma aplicação
 *  do EasyFramework.
 */
class App {

    /**
     * Holds the location of each class
     * @var array
     */
    protected static $_classMap = array();

    /**
     * Maps an old style class type to the corresponding package
     * @var array
     */
    public static $legacy = array();

    /**
     * Sets up each package location on the file system. You can configure multiple search paths
     * for each package, those will be used to look for files one folder at a time in the specified order
     * All paths should be terminated with a Directory separator
     *
     * Usage:
     *
     * `App::build(array(Model' => array('/a/full/path/to/models/'))); will setup a new search path for the Model package`
     *
     * `App::build(array('Model' => array('/path/to/models/')), App::RESET); will setup the path as the only valid path for searching models`
     *
     * `App::build(array('View/Helper' => array('/path/to/helpers/', '/another/path/'))); will setup multiple search paths for helpers`
     *
     * If reset is set to true, all loaded plugins will be forgotten and they will be needed to be loaded again.
     *
     * @param array $paths associative array with package names as keys and a list of directories for new search paths
     * @param mixed $mode App::RESET will set paths, App::APPEND with append paths, App::PREPEND will prepend paths, [default] App::PREPEND
     * @return void
     */
    public static function build($paths = array()) {
        self::$legacy = array(
            //Framework Rotes
            "Vendors" => array(
                APP_PATH . "Vendors",
                FRAMEWORK_PATH . "Vendors"
            ),
            //Core Rotes
            "Datasource" => array(CORE . 'Model' . DS . 'Datasources'),
            //App Rotes
            "Config" => array(APP_PATH . "Config"),
            "Locale" => array(APP_PATH . "Locale"),
            "Controller" => array(
                APP_PATH . "Controller",
                CORE . "Controller"
            ),
            "Model" => array(
                APP_PATH . "Model",
                CORE . "Model"
            ),
            "View" => array(
                APP_PATH . "View" . DS . "Pages",
                CORE . "View"
            ),
            "Component" => array(
                APP_PATH . 'Controller' . DS . "Component",
                CORE . 'Controller' . DS . "Component"
            ),
            "Helper" => array(
                APP_PATH . 'View' . DS . "Helper",
                CORE . 'View' . DS . "Helper"
            ),
            "Layout" => array(
                APP_PATH . "View" . DS . "Layouts",
                CORE . "View" . DS . "Layouts"
            ),
            "Element" => array(
                APP_PATH . "View" . DS . "Elements",
                CORE . "View" . DS . "Elements"
            )
        );
    }

    /**
     * Declares a package for a class. This package location will be used
     * by the automatic class loader if the class is tried to be used
     *
     * Usage:
     *
     * `App::uses('MyCustomController', 'Controller');` will setup the class to be found under Controller package
     *
     * `App::uses('MyHelper', 'MyPlugin.View/Helper');` will setup the helper class to be found in plugin's helper package
     *
     * @param string $className the name of the class to configure package for
     * @param string $location the package name
     * @return void
     */
    public static function uses($className, $location) {
        self::$_classMap[$className] = $location;
    }

    /**
     * Method to handle the automatic class loading. It will look for each class' package
     * defined using App::uses() and with this information it will resolve the package name to a full path
     * to load the class from. File name for each class should follow the class name. For instance,
     * if a class is name `MyCustomClass` the file name should be `MyCustomClass.php`
     *
     * @param string $className the name of the class to load
     * @return boolean
     */
    public static function load($className) {
        if (!isset(self::$_classMap[$className])) {
            return false;
        }
        App::import(self::$_classMap[$className], $className);
    }

    /**
     * Obtêm a versão do core
     * @return string 
     */
    public static function getVersion() {
        $ini = parse_ini_file(CORE . "version.ini");
        return $ini['version'];
    }

    /**
     *  Importa um ou mais arquivos em uma aplicação.
     *
     *  @param string $type Tipo do arquivo a ser importado
     *  @param mixed $file String com o nome de um arquivo ou array com vários arquivos
     *  @param string $ext Extensção do(s) arquivo(s) a ser(em) importado(s)
     *  @return mixed Arquivo incluído ou falso em caso de erro
     */
    public static function import($type = "Core", $file = null, $ext = "php") {
        if (is_array($file)) {
            foreach ($file as $file) {
                $include = self::import($type, $file, $ext);
            }
            return $include;
        } else {
            $file_path = self::path($type, $file, $ext);
            if ($file_path) {
                return (bool) include_once $file_path;
            }
        }
        return false;
    }

    /**
     *  Retorna o caminho completo de um arquivo dentro da aplicação.
     *
     *  @param string $type Tipo do arquivo a ser buscado
     *  @param string $file Nome do arquivo a ser buscado
     *  @param string $ext Extensão do arquivo a ser buscado
     *  @return mixed Caminho completo do arquivo ou falso caso não exista
     */
    public static function path($type = "Core", $file = null, $ext = "php") {
        $parts = explode("/", $type);
        $originalPath = isset(self::$legacy[$parts[0]]) ? self::$legacy[$parts[0]] : $type;

        if (is_array($originalPath)) {

            $extra = self::_extractTypesPaths($parts);

            foreach ($originalPath as $path) {
                if (!is_null($file)) {
                    $file_path = $path . $extra . DS . "{$file}.{$ext}";
                } else {
                    $file_path = $path . $extra . DS;
                }
                if (file_exists($file_path)) {
                    return $file_path;
                }
            }
        } else {
            if (!is_null($file)) {
                $file_path = CORE . $type . DS . "{$file}.{$ext}";
            } else {
                $file_path = CORE . $type . DS;
            }
            if (file_exists($file_path)) {
                return $file_path;
            }
        }
        return false;
    }

    private static function _extractTypesPaths(Array $parts) {
        $extra = "";
        if (count($parts) > 1) {
            for ($i = 1; $i <= count($parts) - 1; $i++) {
                $extra .= DS . $parts[$i];
            }
        }
        return $extra;
    }

}

?>
