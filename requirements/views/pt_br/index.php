<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="content-language" content="pt"/>
        <link rel="stylesheet" type="text/css" href="css/main.css" />
        <title>Verificador de requisitos do EasyFramework</title>
    </head>

    <body>
        <div id="page">

            <div id="header">
                <h1>Verificador de requisitos do EasyFramework</h1>
            </div><!-- header-->

            <div id="content">
                <h2>Descrição</h2>
                <p>
                    Este script verifica se as configurações do servidor satisfazem os requisitos
                    para executar aplicações Web que utilizem o <a href="http://www.easy.lellysinformatica.com/"> EasyFramework </a>.
                    É verificado se o servidor está executando a versão correta do PHP,
                    se as extensões apropriadas do PHP foram carregadas,
                    e se as definições do arquivo php.ini estão corretas.
                </p>

                <h2>Resultados</h2>
                <p>
                    <?php if ($result > 0): ?>
                        Parabéns! As configurações do seu servidor satisfazem todos os requisitos do EasyFramework.
                    <?php elseif ($result < 0): ?>
                        As configurações do seu servidor satisfazem os requisitos mínimos do EasyFramework. Por favor, preste atenção às advertências listados abaixo caso sua aplicação irá utilizar os recursos correspondentes.
                    <?php else: ?>
                        Infelizmente o as configurações do seu servidor não satisfazem os requisitos do EasyFramework.
                    <?php endif; ?>
                </p>

                <h2>Detalhes</h2>

                <table class="result">
                    <tr><th>Nome</th><th>Resultado</th><th>Exigido por</th><th>Detalhe</th></tr>
                    <?php foreach ($requirements as $requirement): ?>
                        <tr>
                            <td>
                                <?php echo $requirement[0]; ?>
                            </td>
                            <td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
                                <?php echo $requirement[2] ? 'OK' : ($requirement[1] ? 'Falhou' : 'Advertência'); ?>
                            </td>
                            <td>
                                <?php echo $requirement[3]; ?>
                            </td>
                            <td>
                                <?php echo $requirement[4]; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <table>
                    <tr>
                        <td class="passed">&nbsp;</td><td>OK</td>
                        <td class="failed">&nbsp;</td><td>Falhou</td>
                        <td class="warning">&nbsp;</td><td>Advertência</td>
                    </tr>
                </table>

            </div><!-- content -->

            <div id="footer">
                <?php echo $serverInfo; ?>
            </div><!-- footer -->

        </div><!-- page -->
    </body>
</html>