<?php

/**
 *  Config é a classe que toma conta de todas as configuração necessárias para
 *  uma aplicação.
 */
class Config {

    private static $config = array();

    /**
     *  Retorna o valor de uma determinada chave de configuração.
     *
     *  @param string $key Nome da chave da configuração
     *  @return mixed Valor de configuração da respectiva chave
     */
    public static function read($key) {
        return array_key_exists($key, self::$config) ? self::$config[$key] : null;
    }

    /**
     *  Grava o valor de uma configuração da aplicação para determinada chave.
     *
     *  @param string $key Nome da chave da configuração
     *  @param string $value Valor da chave da configuração
     *  @return boolean true
     */
    public static function write($key, $value) {
        self::$config[$key] = $value;
    }

}

?>
