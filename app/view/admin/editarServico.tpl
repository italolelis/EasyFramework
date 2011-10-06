{include file=$header}
<header class="container_12 clearfix">
    <div class="grid_12">
        <h1>Editar Serviço</h1>
    </div>
</header>
<section class="container_12 clearfix">
    <div class="portlet grid_12">
        <header>
            <h2>Editar serviço</h2>
        </header>
        <section>
            <div id="info" class="message success"> 
                <h3>Sucesso!</h3> 
                <p></p> 
            </div>

            <form class="form has-validation">
                <div class="clearfix">
                    <label for="nome" class="form-label">Nome <em>*</em><small>Digite o nome do serviço</small></label>
                    <div class="form-input"><input type="text" name="nome" class=":required" value="{$servico->getNome()}"/></div>
                </div>

                <div class="clearfix">
                    <button class="button" type="submit">Confirmar</button>
                    <button onclick="location.href = '{$url.servicos}'; return false;">Voltar</button>
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
                servico = new Crud('servicos', '', '')
            });
                
           jQuery('form:first').submit(function(){
    {/literal}
              var dataString = $(this).serialize() + "&id=" + {$servico->getId()} + "";
    {literal}
              servico.update(dataString); 
                  
              return false;    
            });
                
    </script> 
{/literal}
<!-- DATATABLES END -->
{include file=$footer}