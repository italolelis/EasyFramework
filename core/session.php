<?php

/**
 *  Controle das sessões do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
class Session extends Object {

    /**
     *  Inicializa as sessões para gerenciamento.
     *
     *  @return boolean Verdadeiro para sessão criada
     */
    public static function start() {
        return session_start();
    }

    /**
     *  Destroi as sessões para gerenciamento.
     *
     *  @return boolean Verdadeiro para sessão destruida
     */
    public static function destroy() {
        return session_destroy();
    }

    /**
     *  Verifica se a sessão foi criada com sucesso.
     *
     *  @return boolean Verdadeiro para sessão criada
     */
    public static function started($key = null) {
        if ($key != null)
            return isset($_SESSION[$key]);
        else
            return isset($_SESSION);
    }

    /**
     *  Lê uma variável setada pela sessão.
     * 
     *  @param string $name Variável a ser retornada
     *  @return string Valor da variável solicitada
     */
    public static function read($name) {
        if (!self::started())
            self::start();
        return $_SESSION[$name];
    }

    /**
     *  Escreve uma variável com seu respectivo valor na sessão.
     *
     *  @param string $name Valor da variável
     *  @param string $value Conteudo da variável
     */
    public static function write($name, $value) {
        if (!self::started())
            self::start();
        $_SESSION[$name] = $value;
    }

    /**
     *  Remove uma variável setada na sessão.
     *
     *  @param string $name Variável a ser removida
     *  @return boolean Verdadeiro para remoção da variável
     */
    public static function delete($name) {
        if (!self::started())
            self::start();
        unset($_SESSION[$name]);
        return true;
    }

}

?>