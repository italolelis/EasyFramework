<!DOCTYPE html>
<!--[if IE 7 ]>   <html lang="en" class="ie7 lte8"> <![endif]--> 
<!--[if IE 8 ]>   <html lang="en" class="ie8 lte8"> <![endif]--> 
<!--[if IE 9 ]>   <html lang="en" class="ie9"> <![endif]--> 
<!--[if gt IE 9]> <html lang="en"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!--[if lt IE 9 ]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
        <title>Agendamento Online - Login</title>

        <!-- STYLESHEETS -->
        {$css.geral}
        <!--[if lt IE 9]>
        <link rel="stylesheet" media="screen" href="css/ie.css" />
        <![endif]-->
        <!-- STYLESHEETS END -->

        <!--[if lt IE 9]>
        <script src="js/html5.js"></script>
        <script type="text/javascript" src="js/selectivizr.js"></script>
        <![endif]-->

    </head>
    <body class="login" style="overflow: hidden;">
        <div id="loading"> 
            <script type = "text/javascript"> 
                document.write("<div id='loading-container'><p id='loading-content'>" +
                               "<img id='loading-graphic' width='16' height='16' src='images/ajax_loader_big_000000.gif' /> " +
                               "Carregando...</p></div>");
            </script> 
        </div> 

        <div class="login-box">
            <section class="portlet login-box-top">
                <header>
                    <h2 class="ac">Agendamento Online - Login</h2>
                </header>
                <section>
                    <div id="info" class="message info">
                        <h3>Informação</h3>
                        <p></p>
                    </div>
                    <form class="has-validation" style="margin-top: 30px">
                        <p style="margin-bottom: 30px">
                            <input type="text" id="username" class="full" value="" name="username" required="required" placeholder="Username" />
                        </p>
                        <p style="margin-bottom: 30px">
                            <input type="password" id="password" class="full" value="" name="password" required="required" placeholder="Password" />
                        </p>
                        <p class="clearfix">
                            <span class="fl" style="line-height: 23px;">
                                <label class="choice" for="remember">
                                    <input type="checkbox" id="remember" class="" value="1" name="remember"/>
                                    Lembrar-me
                                </label>
                            </span>

                            <button class="fr" type="submit">Login</button>
                        </p>
                    </form>
                    <footer class="ac">
                        <a href="#" class="button" onclick="location.href = '{$url.agendamento}'; return false;">Esqueceu sua senha?</a>
                        <a href="#" class="button" onclick="location.href = '{$url.addUsuario}'; return false;">Registrar-se</a>
                    </footer>
                </section>
            </section>
        </div>

        <!-- MAIN JAVASCRIPTS -->
        {$js.footer}
        <!--[if lt IE 9]>
        <script type="text/javascript" src="js/PIE.js"></script>
        <script type="text/javascript" src="js/ie.js"></script>
        <![endif]-->    
        <!-- MAIN JAVASCRIPTS END -->

        <!-- LOADING SCRIPT -->
        {literal}
            <script type="text/javascript">
                $(window).load(function(){
                    $("#loading").fadeOut(function(){
                        $(this).remove();
                        $('body').removeAttr('style');
                    });
                });
                
                jQuery('form:first').submit(function(){
                     var request = new Request();
               
                    jQuery.ajax({
                        type: "POST",
                        url: 'login-login',
                        data: $('form:first').serialize(),
                        beforeSend:function(){
                            request.wait();
                        },
                        success: function(msg) { 
                            if(msg)
                            {
                                request.error(msg);
                            }else{
                                location.href = '{/literal}{$url.home}{literal}';
                            }
                        },
                        error: function(){
                            request.error();
                        }
                    });
                return false;
            });
            </script>
        {/literal}
        <!-- LOADING SCRIPT -->

    </body>

</html>