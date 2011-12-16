<?php

/**
 *  Security cuida de vários aspectos relacionados à segurança de sua aplicação,
 *  fazendo encriptação/decriptação e hashing de dados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
class Security {

    /**
     *  Encripta/decripta um valor usando a chave especificada.
     *
     *  @param string $text Valor a ser encriptado/decriptado
     *  @param string $key Chage a ser usada para encriptar/decriptar o valor
     *  @return string Valor encriptado/decriptado
     *  @copyright Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
     */
    public static function cipher($text, $key) {
        if (empty($key)):
            trigger_error("You cannot use an empty key for Security::cipher()", E_USER_WARNING);
            return null;
        endif;
        if (!defined("CIPHER_SEED")):
            define("CIPHER_SEED", "76859309657453542496749683645");
        endif;
        srand(CIPHER_SEED);
        $output = "";
        for ($i = 0; $i < strlen($text); $i++):
            for ($j = 0; $j < ord(substr($key, $i % strlen($key), 1)); $j++):
                rand(0, 255);
            endfor;
            $mask = rand(0, 255);
            $output .= chr(ord(substr($text, $i, 1)) ^ $mask);
        endfor;
        return $output;
    }

    /**
     *  Cria um hash de uma string usando o método especificado.
     *
     *  @param string $data Text to be cript
     *  @param string $hash Hash Algorithim 
     *  @param mixed $raw_output Case sensitive
     *  @return string Hash value
     */
    public static function hash($data, $hash = "md5", $raw_output = null) {
        return hash($hash, $data, $raw_output);
    }

    public static function token() {
        return $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . Session::id();
    }

}

?>