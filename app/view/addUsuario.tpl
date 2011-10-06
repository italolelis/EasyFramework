<html>
    <head>
        <meta charset="utf-8">
        <!--[if lt IE 9 ]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
        <title>Agendamento Online - Cadastrar</title>

        {$css.geral}
        <!-- SCROLLABLE WIZARD CSS -->
        <link rel="stylesheet" media="screen" href="css/scrollable_wizard.css" />
        <!-- SCROLLABLE WIZARD CSS END -->
    </head>
    <body>
        <section class="container_12 clearfix">
            <div class="grid_12">
                <!-- the form --> 
                <form novalidate>
                    <div id="wizard"> 
                        <nav>
                            <ul id="status"> 
                                <li class="active"><strong>1.</strong> Criar Conta</li> 
                                <li><strong>2.</strong> Informações de Contato</li> 
                                <li><strong>3.</strong> Finalizar</li> 
                            </ul>
                        </nav>

                        <div class="items"> 

                            <!-- page1 --> 
                            <section class="page"> 

                                <header> 
                                    <h2>
                                        <strong>Passo 1: </strong> Informações da Conta
                                        <em>Digite suas informações de login:</em> 
                                    </h2>
                                </header> 

                                <section>
                                    <ul class="clearfix">
                                        <!-- email --> 
                                        <li class="required"> 
                                            <label> 
                                                <strong>1.</strong> Digite sua endereço de e-mail <span>*</span><br /> 
                                                <input type="text" class="full" name="email" required/>
                                                <em>Seu email para contato. Todos os agendamentos serão confirmados por esse email.</em> 
                                            </label> 
                                        </li> 

                                        <!-- username --> 
                                        <li> 
                                            <label> 
                                                <strong>2.</strong> Escolha um usuário <br /> 
                                                <input type="text" class="full" name="username" /> 
                                                <em>Um nome de usuário para ser usado como seu login.</em> 
                                            </label> 
                                        </li> 

                                        <!-- password --> 
                                        <li class="double"> 

                                            <label> 
                                                <strong>3.</strong> Escolha uma senha <span>*</span><br /> 
                                                <input type="password" class="full" name="password" required /> 
                                                <em>Deve conter pelo menos 6 caracteres.</em> 
                                            </label> 

                                            <label> 
                                                Verifique a senha <span>*</span><br /> 
                                                <input type="password" class="full" name="password1" required /> 
                                            </label> 
                                        </li> 
                                    </ul>
                                </section>

                                <footer class="clearfix">
                                    <button type="button" class="next fr">Presseguir &raquo;</button> 
                                </footer>

                            </section> 

                            <!-- page2 --> 
                            <section class="page"> 

                                <header>
                                    <h2>
                                        <strong>Passo 2: </strong> Informações de contato <b></b> 
                                        <em>Diga-nos onde você mora:</em> 
                                    </h2>
                                </header>

                                <section>
                                    <ul class="clearfix">
                                        <!-- address --> 
                                        <li> 
                                            <label> 
                                                <strong>1.</strong> Digite seu Nome <span>*</span><br /> 
                                                <input type="text" class="full" name="nome" required /> 
                                                <em>O seu nome completo</em> 
                                            </label>
                                        </li>

                                        <li> 
                                            <label> 
                                                <strong>2.</strong> Digite seu telefone <span>*</span><br /> 
                                                <input type="text" class="full" name="tel" required /> 
                                                <em>Um telefone para contato</em> 
                                            </label>
                                        </li> 
                                    </ul> 
                                </section>

                                <footer class="clearfix">
                                    <button type="button" class="prev fl">&laquo; Voltar</button> 
                                    <button type="submit" class="next fr">Prosseguir &raquo;</button> 
                                </footer>

                            </section> 

                            <!-- page3 --> 
                            <section class="page"> 

                                <header> 
                                    <h2>
                                        <strong>Passo 3: </strong> Parabéns!
                                        <em>Você está registrado e já pode começar a utilizar nossos serviços.</em>
                                    </h2>
                                </header> 

                                <section>
                                    <h3>Obrigado por registrar-se!</h3>
                                    <button type="button" onclick="location.href = '{$url.login}'; return false;">Fazer Login</button> 
                                </section>

                                <footer class="clearfix">
                                    <button type="button" class="prev">&laquo; Voltar</button> 
                                </footer>

                            </section> 


                        </div><!--items--> 
                    </div><!--wizard--> 
                </form> 

            </div>
        </section>


        <!-- MAIN JAVASCRIPTS -->
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/jquery.tools.min.js"></script>
        <script type="text/javascript" src="js/jquery.uniform.min.js"></script>
        <!--[if lt IE 9]>
        <script type="text/javascript" src="js/PIE.js"></script>
        <script type="text/javascript" src="js/ie.js"></script>
        <![endif]-->

        <script type="text/javascript" src="js/global.js"></script>
        <script type="text/javascript" src="js/custom.js"></script>
        <!-- MAIN JAVASCRIPTS END -->

        {literal}
             <!-- LOADING SCRIPT -->
            <script>
                $(window).load(function(){
                    $("#loading").fadeOut(function(){
                        $(this).remove();
                        $('body').removeAttr('style');
                        });
                });
            
                jQuery('form:first').submit(function(){
                  usuario = new Crud('usuarios', '');    
                  var dataString = $(this).serialize();
                  usuario.add(dataString);
                  
                  return false;    
                });

                $(function() {
                    var root = $("#wizard").scrollable();
         
                    // some variables that we need
                    var api = root.scrollable();
        
                    // validation logic is done inside the onBeforeSeek callback
                    api.onBeforeSeek(function(event, i) {

                        // we are going 1 step backwards so no need for validation
                        if (api.getIndex() < i) {

                            // 1. get current page
                            var page = root.find(".page").eq(api.getIndex()),

                                 // 2. .. and all required fields inside the page
                                 inputs = page.find("[required]").removeClass("error"),

                                 // 3. .. which are empty
                                 empty = inputs.filter(function() {
                                    return $(this).val().replace(/\s*/g, '') == '';
                                 });

                             // if there are empty fields, then
                            if (empty.length) {
                                // add a CSS class name "error" for empty & required fields
                                empty.addClass("error");
         
                                // cancel seeking of the scrollable by returning false
                                return false;
                            }
         
                        }

                        // update status bar
                        $("#status li").removeClass("active").eq(i).addClass("active");

                    });
         
                    // if tab is pressed on the next button seek to next page
                    root.find("button.next").keydown(function(e) {
                        if (e.keyCode == 9) {
         
                            // seeks to next tab by executing our validation routine
                            api.next();
                            e.preventDefault();
                        }
                    });
                });
            </script> 
            <!-- WIZARD SETUP END -->
        {/literal}

    </body>
</html>