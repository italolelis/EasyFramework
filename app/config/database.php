<?php

/**
 * Aqui você deve definir suas configurações de banco de dados, todas de acordo
 * com um determinado ambiente de desenvolvimento. Você pode definir quantos
 * ambientes forem necessários.
 * 
 */
Config::write("datasource", array(
    "desenvolvimento" => array(
        "driver" => "mysql",
        "host" => "localhost",
        "user" => "root",
        "password" => "",
        "database" => "dbagendamento",
        "prefix" => ""
    ),
    "producao" => array(
        "driver" => "mysql",
        "host" => "localhost",
        "user" => "lellysin",
        "password" => "(9R0]*d%UR%H",
        "database" => "dbagendamento",
        "prefix" => ""
    )
));
?>
