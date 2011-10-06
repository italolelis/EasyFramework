<?php

/**
 * Coloque suas funções customizadas nesse arquivo. Ele será carreado antes de qualquer requisição e poderá
 * ser chamado em qualquer lugar da app, sem precisar importar o arquivo.
 *
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright 2011, EasyFramework
 *
 */

/**
 * Verifica se o usuário está logado
 * @return mixed 
 */
function isLogedIn() {
    if (!Session::started('usuarios'))
        header("Location: " . LOGIN_URL);
    else
        return false;
}

/**
 * Verifica se o usuario está logado na pagina index
 * @return mixed 
 */
function isNotLogedIn() {
    if (Session::started('usuarios'))
        header("Location: " . HOME_URL);
    else
        return false;
}

?>
