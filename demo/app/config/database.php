<?php

/**
 *  Com o environment, você pode escolher qual ambiente de desenvolvimento está
 *  utilizando. É principalmente utilizado na configurção de banco de dados,
 *  evitando que você tenha que redefiní-las a cada deploy.
 */
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

/**
 * Com o debug configurado para true, você receberá mensagens que lhe ajudam a encontrar o erro
 * de forma mais amigável. Caso esteja setado como false, o usuário verá páginas de erro 404 e 
 * 500 para não sabe o tipo de erro (caso exista).
 */
Config::write("debug", true);
/**
 * Aqui você deve definir suas configurações de banco de dados, todas de acordo
 * com um determinado ambiente de desenvolvimento. Você pode definir quantos
 * ambientes forem necessários.
 * 
 */
Config::write("datasource", array(
    "development" => array(
        "driver" => "mysql",
        "host" => "localhost",
        "user" => "root",
        "password" => "",
        "database" => "dbdemo",
        "prefix" => ""
    ),
    "production" => array(
        "driver" => "mysql",
        "host" => "localhost",
        "user" => "theUsername",
        "password" => "justAPassToProduction",
        "database" => "theDatabaseNameAtProduction",
        "prefix" => ""
    )
));
?>
