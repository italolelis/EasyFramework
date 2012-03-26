<?php

/**
 * Essa rota define o controller padrão de sua aplicação, aquele que o usuário
 * verá toda vez que acessar a raíz de seu sistema. Você pode escolher o controller
 * que mais fizer sentido para você
 */
//Mapper::setRoot("home");

/**
 *  Template é onde você poderá configurar o comportamento do seu template(views).
 *  Nele podem ser setadas configurações como: Cacheble: se um template será guardado
 *  em cache, Urls: Define as urls que serão passadas para a view.
 */
Config::write("View.layouts", array(
    "layout" => "layout.tpl"
));
Config::write("View.elements", array(
        //any elements that you want to put at your views or templates
));

//Configuramos as urls que serão usuadas nas views
Config::write("View.urls", array(
    'users' => 'users',
    'addUser' => 'users/add',
    'editUser' => 'users/edit',
    'deleteUser' => 'users/delete',
));
?>
