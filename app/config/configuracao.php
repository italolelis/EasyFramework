<?php

/**
 *  Esse é o arquivo das principais configurações do EasyFramework. Através delas,
 *  você pode configurar o comportamento do núcleo do EasyFramework.
 */
/**
 *  Com o environment, você pode escolher qual ambiente de desenvolvimento está
 *  utilizando. É principalmente utilizado na configurção de banco de dados,
 *  evitando que você tenha que redefiní-las a cada deploy.
 */
Config::write("environment", "desenvolvimento");

/**
 *  Template é onde você poderá configurar o comportamento do seu template(views).
 *  Nele podem ser setadas configurações como: Cacheble: se um template será guardado
 *  em cache, Urls: Define as urls que serão passadas para a view.
 */
Config::write("template", array(
    "header" => "header.tpl",
    "footer" => "footer.tpl",
    //Definimos se o cache será ativado no template
    "cacheble" => true,
    //Configuramos as urls que serão usuadas nas views
    "urls" => array(
        'home' => 'site-home',
        'login' => 'site-index',
        'logout' => 'login-logout',
        'site' => "http://www.lellysinformatica.com",
        'agendamento' => 'agendamento-index',
        'calendario' => 'agendamento-showCalendar',
        'usuarios' => 'usuarios-index',
        'servicos' => 'servicos-index',
        'addAgendamento' => 'agendamento-addAgendamento',
        'addUsuario' => 'usuarios-addUsuario',
        'addServico' => 'servicos-addServico',
        'editarAgendamento' => 'agendamento-editarAgendamento-',
        'editarServico' => 'servicos-editarServico-',
        'editarUsuario' => 'usuarios-editarUsuario-',
        'editarSenha' => 'usuarios-updatePassword-'
    ),
    //Configuramos o css que será passado para as views
    "css" => array(
        'geral' => HtmlHelper::stylesheet(array(
            'reset.css',
            'grids.css',
            'style.css',
            'jquery.uniform.css',
            'forms.css',
            'themes/lightblue/style.css',
            'scrollable_wizard.css'
        )),
        'datatables' => HtmlHelper::stylesheet(array(
            'datatables/css/cleanslate.css',
            'fullcalendar/fullcalendar.css'
                ), 'lib/')
    ),
    //Configuramos os js que serão passados para as views
    "js" => array(
        'footer' => HtmlHelper::script(array(
            'jquery.min.js',
            'jquery.tools.min.js',
            'jquery.uniform.min.js',
            'global.js',
            'vanadium.js',
            'custom.js'
        )))
));
?>
