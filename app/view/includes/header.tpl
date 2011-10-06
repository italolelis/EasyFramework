<!DOCTYPE html>
<!--[if IE 7 ]>   <html lang="pt-BR" class="ie7 lte8"> <![endif]--> 
<!--[if IE 8 ]>   <html lang="pt-BR" class="ie8 lte8"> <![endif]--> 
<!--[if IE 9 ]>   <html lang="pt-BR" class="ie9"> <![endif]--> 
<!--[if gt IE 9]> <html lang="pt-BR"> <![endif]-->
<!--[if !IE]><!--> <html lang="pt-BR"> <!--<![endif]-->

    <head>
        <meta charset="utf-8">
        <!--[if lt IE 9 ]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
        <title>Agendamento Online</title>

        <link rel="shortcut icon" href="favicon.html">
        <!-- STYLESHEETS -->
        {$css.geral}
        {$css.datatables}
        <!--[if lt IE 9]>
        <link rel="stylesheet" media="screen" href="css/ie.css" />
        <![endif]-->
        <!-- STYLESHEETS END -->

        <!--[if lt IE 9]>
        <script src="js/html5.js"></script>
        <script type="text/javascript" src="js/selectivizr.js"></script>
        <![endif]-->
    </head>

    <body style="overflow: hidden;">
        <div id="loading">
            <script type = "text/javascript">
                document.write("<div id='loading-container'><p id='loading-content'>" +
                               "<img id='loading-graphic' width='16' height='16' src='images/ajax_loader_big_000000.gif' /> " +
                               "Carregando...</p></div>");
            </script>
        </div>

        <div id="wrapper">
            <header>
                <h1><a href="{$url.home}">Agendamento Online</a></h1>
                <nav>
                    <ul id="main-navigation" class="clearfix"> 
                        {if $pagina == $url.agendamento || $pagina == $url.addAgendamento || $pagina == $url.calendario}
                            <li class="dropdown active">
                            {else}
                            <li class="dropdown">
                            {/if}
                            <a href="#a">Arquivo</a>
                            <ul>
                                <li class="dropdown">
                                    <a href="#ab">Agendamento</a>
                                    <ul>
                                        <li><a href="{$url.agendamento}">Listagem</a></li>
                                        <li><a href="{$url.addAgendamento}">Adicionar Novo</a></li>
                                        <li><a href="{$url.calendario}">Calendário</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a href="#">menu item</a>
                                    <ul>
                                        <li><a href="#">menu item</a></li>
                                        <li><a href="#">menu item</a></li>
                                        <li><a href="#">menu item</a></li>
                                        <li><a href="#">menu item</a></li>
                                        <li><a href="#">menu item</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        {if $pagina == $url.usuarios || $pagina == $url.servicos}
                            <li class="dropdown active">
                            {else}
                            <li class="dropdown">
                            {/if}
                            {if $sessao.admin}
                                <a href="#">Sistema</a>
                                <ul>
                                    <li class="dropdown">
                                        <a href="#ab">Usuários</a>
                                        <ul>
                                            <li><a href="{$url.usuarios}">Listagem</a></li>
                                            <li><a href="{$url.addUsuario}">Adicionar Novo</a></li>
                                        </ul>
                                    </li>
                                    <li class="dropdown">
                                        <a href="#">Servicos</a>
                                        <ul>
                                            <li><a href="{$url.servicos}">Listagem</a></li>
                                            <li><a href="{$url.addServico}">Adicionar Novo</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            {/if}       
                        </li>
                        <li class="fr dropdown">
                            <a href="#" class="with-profile-image"><span><img src="images/profile_image.png" /></span>{$sessao.usuario}</a>
                            <ul>
                                <li><a href="{$url.editarUsuario}{$sessao.id}">Minha Conta</a></li>
                                {if $sessao.admin}
                                    <li><a href="{$url.usuarios}">Usuários</a></li>
                                {/if}
                                <li><a href="{$url.logout}">Sair</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </header>
            <section>

                <!-- Sidebar -->
                <aside>
                    <nav>
                        <ul>
                            {if $pagina == $url.home}
                                <li class="current"><a href="{$url.home}">Painel de Controle</a></li>
                            {else}
                                <li><a href="{$url.home}">Painel de Controle</a></li>
                            {/if}

                            {if $pagina == $url.agendamento || $pagina == $url.addAgendamento}
                                <li class="current"><a href="{$url.agendamento}">Agendamento</a></li>
                            {else}
                                <li><a href="{$url.agendamento}">Agendamento</a></li>
                            {/if}
                            {if $sessao.admin}
                                {if $pagina == $url.usuarios || $pagina == $url.addUsuario}
                                    <li class="current"><a href="{$url.usuarios}">Usuários</a></li>
                                {else}
                                    <li><a href="{$url.usuarios}">Usuários</a></li>
                                {/if}
                            {/if}
                        </ul>
                    </nav>
                    <nav>
                        <h2>Aplicações</h2>
                        <ul>
                            {if $pagina == $url.calendario}
                                <li class="current"><a href="{$url.calendario}">Calendário</a></li>
                            {else}
                                <li><a href="{$url.calendario}">Calendário</a></li>
                            {/if}
                            <li><a href="wysiwyg.html">WYSIWYG Editor</a></li>
                        </ul>
                    </nav>
                </aside>
                <!-- Sidebar End -->

                <section>