{include file=$header}
<header class="container_12 clearfix">
    <div class="grid_12">
        <h1>Nova Senha</h1>
    </div>
</header>
<section class="container_12 clearfix">
    <div class="portlet grid_12">
        <header>
            <h2>Resetar Senha</h2>
        </header>
        <section>
            <div id="info" class="message success"> 
                <h3>Sucesso!</h3> 
                <p></p> 
            </div>

            <form id="formAgendamento" class="form has-validation">
                <div class="clearfix">
                    <label for="pass" class="form-label">Senha <em>*</em><small>Digite sua nova senha</small></label>
                    <div class="form-input"><input type="password" id="pass" name="password" class=":required" maxLength="16" /></div>
                </div>

                <div class="clearfix">
                    <label for="passConfirm" class="form-label">Confirmar Senha <em>*</em><small>Confirme a nova senha</small></label>
                    <div class="form-input"><input type="password" id="passConfirm" name="passConfirm" class=":same_as;pass" mimaxLength="16"/></div>
                </div>

                <div class="clearfix">
                    <button type="submit" id="submit">Confirmar</button>
                    <button onclick="location.href = '{$url.editarUsuario}{$id}'; return false;">Voltar</button>
                </div>
            </form>

        </section>
    </div>

</section>
<!-- DATATABLES -->
{literal}
    <script type="text/javascript"> 
            $(document).ready(function(){
                //escondemos as mensagens da página
                $('.success').hide();     

                //objeto crud
                usuario = new Crud('usuarios', '', '')
            });
           
               
           jQuery('form:first').submit(function(){ 
    {/literal}
              var dataString = $(this).serialize() + "&id=" + {$id} + "";
    {literal}
              usuario.update(dataString, 'updatepass'); 
                  
              return false;    
            });
                
    </script> 
{/literal}
<!-- DATATABLES END -->
{include file=$footer}