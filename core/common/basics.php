<?php

/**
 *  Object é a classe abstrata herdada por todas as outras classes do EasyFramework,
 *  provendo funcionalidade básica para o framework.
 */
abstract class Object {

    /**
     *  Loga os eventos processados pelo framework.
     * 
     *  @param string $message Mensagem do log
     *  @return string Retorna a mensagem a ser trabalhada
     */
    protected function log($message = "") {
        return $message;
    }

    /**
     *  Paraliza a execução do script atual.
     * 
     *  @param string $status
     */
    protected function stop($status = null) {
        exit($status);
    }

}

/**
 *  App cuida de tarefas relativas a importação de arquivos dentro de uma aplicação
 *  do EasyFramework.
 */
class App extends Object {

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
    public static function import($type = "Core", $file = "", $ext = "php") {
        if (is_array($file)) {
            foreach ($file as $file) {
                $include = self::import($type, $file, $ext);
            }
            return $include;
        } else {
            if ($file_path = self::path($type, $file, $ext)) {
                return require_once $file_path;
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
    public static function path($type = "Core", $file = "", $ext = "php") {
        $paths = array(
            "Core" => array(CORE),
            "App" => array(APP_PATH),
            "Lib" => array(CORE . "lib"),
            "Component" => array(CORE . "controller/components"),
            "Datasource" => array(CORE . "model/datasources"),
            "Config" => array(APP_PATH . "config"),
            "Controller" => array(APP_PATH . "controllers"),
            "Model" => array(APP_PATH . "models"),
            "View" => array(APP_PATH . "view"),
        );

        foreach ($paths[$type] as $path) {
            $file_path = $path . DS . "{$file}.{$ext}";
            if (file_exists($file_path)) {
                return $file_path;
            }
        }
        return false;
    }

}

/**
 *  Config é a classe que toma conta de todas as configuração necessárias para
 *  uma aplicação.
 */
class Config extends Object {

    /**
     *  Definições de configuração.
     *
     *  @var array
     */
    private $config = array();

    /**
     *  Retorna uma única instância (Singleton) da classe solicitada.
     *
     *  @staticvar object $instance Objeto a ser verificado
     *  @return object Objeto da classe utilizada
     */
    public static function &getInstance() {
        static $instance = array();
        if (!isset($instance[0]) || !$instance[0]):
            $instance[0] = new Config();
        endif;
        return $instance[0];
    }

    /**
     *  Retorna o valor de uma determinada chave de configuração.
     *
     *  @param string $key Nome da chave da configuração
     *  @return mixed Valor de configuração da respectiva chave
     */
    public static function read($key = "") {
        $self = self::getInstance();
        return array_key_exists($key, $self->config) ? $self->config[$key] : null;
    }

    /**
     *  Grava o valor de uma configuração da aplicação para determinada chave.
     *
     *  @param string $key Nome da chave da configuração
     *  @param string $value Valor da chave da configuração
     *  @return boolean true
     */
    public static function write($key = "", $value = "") {
        $self = self::getInstance();
        $self->config[$key] = $value;
        return true;
    }

}

?>
