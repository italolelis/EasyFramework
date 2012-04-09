<?php

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
        "database" => "databasename",
        "enconding" => "utf8",
        "prefix" => ""
    ),
    "production" => array(
        "driver" => "mysql",
        "host" => "localhost",
        "user" => "theUsername",
        "password" => "justAPassToProduction",
        "database" => "theDatabaseNameAtProduction",
        "enconding" => "utf8",
        "prefix" => ""
    )
));
