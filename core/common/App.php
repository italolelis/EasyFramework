<?php

/**
 *  App cuida de tarefas relativas a importação de arquivos dentro de uma aplicação
 *  do EasyFramework.
 */
class App {

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
            if ($file_path = self::path($type, $file, $ext)) {
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

        $paths = array(
            //Framework Rotes
            "EasyRoot" => FRAMEWORK_PATH,
            "Component" => array(FRAMEWORK_PATH . "components"),
            "Helper" => array(FRAMEWORK_PATH . "helpers"),
            //Core Rotes
            "Core" => array(CORE),
            "Lib" => array(CORE . "lib"),
            "Datasource" => array(CORE . "model/datasources"),
            //App Rotes
            "App" => array(APP_PATH),
            "Config" => array(APP_PATH . "config"),
            "Controller" => array(APP_PATH . "controllers"),
            "Model" => array(APP_PATH . "models"),
            "View" => array(APP_PATH . "view"),
            "Layout" => array(APP_PATH . "layouts"),
            "Languages" => array(APP_PATH . "locale")
        );

        foreach ($paths[$type] as $path) {
            if (!is_null($file)) {
                $file_path = $path . DS . "{$file}.{$ext}";
            } else {
                $file_path = $path;
            }
            if (file_exists($file_path)) {
                return $file_path;
            }
        }
        return false;
    }

}

?>