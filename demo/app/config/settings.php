<?php

/**
 *  Esse é o arquivo das principais configurações do EasyFramework. Através delas,
 *  você pode configurar o comportamento do núcleo do EasyFramework.
 */
/**
 * Essa rota define o controller padrão de sua aplicação, aquele que o usuário
 * verá toda vez que acessar a raíz de seu sistema. Você pode escolher o controller
 * que mais fizer sentido para você
 */
Mapper::root("home");
/**
 *  Template é onde você poderá configurar o comportamento do seu template(views).
 *  Nele podem ser setadas configurações como: Cacheble: se um template será guardado
 *  em cache, Urls: Define as urls que serão passadas para a view.
 */
Config::write("template", array(
    "layout" => array(
        "layout" => "layout.tpl"
    ),
    //Configuramos as urls que serão usuadas nas views
    "urls" => array(
        'home' => 'home',
        'usuarios' => 'usuarios',
        'incluirUsuario' => 'usuarios/incluir',
        'editarUsuario' => 'usuarios/edit/',
        'excluirUsuario' => 'usuarios/excluir/',
    )
));
?>
