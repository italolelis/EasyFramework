{include file=$header}
<header class="container_12 clearfix">
    <div class="grid_12">
        <h1>Editar Agendamento</h1>
    </div>
</header>
<section class="container_12 clearfix">
    <div class="portlet grid_12">
        <header>
            <h2>Editar agendamento</h2>
        </header>
        <section>
            <div id="info" class="message success"> 
                <h3>Sucesso!</h3> 
                <p></p> 
            </div>

            <form id="formAgendamento" class="form has-validation">
                <div class="clearfix">
                    <label for="data" class="form-label">Data do Agendamento <em>*</em><small>Digite uma data válida</small></label>
                    <div class="form-input"><input type="date" id="data" name="data" class=":required" OnKeyPress="formatar('##/##/####', this)" maxLength="10" value="{$agendamento->getDia()}"/></div>
                </div>

                <div class="clearfix">
                    <label for="hora" class="form-label">Hora do Agendamento <em>*</em><small>Digite uma hora válida</small></label>
                    <div class="form-input"><input type="text" id="hora" name="hora" class=":required" OnKeyPress="formatar('##:##:##', this)" maxLength="8" value="{$agendamento->getHora()}"/></div>
                </div>

                <div class="clearfix">
                    <label for="form-name" class="form-label">Serviços <small>Selecione os serviços desejados</small></label>
                    <div class="form-input">
                        <ul class="alternate">
                            {html_checkboxes name='servicos' values=$servicos output=$servicos
                                 selected=$servicos_usuario  separator='<li>'}
                        </ul>
                    </div>
                </div>

                <div class="clearfix">
                    <label for="form-name" class="form-label">Status <em>*</em><small>Escolha o status do agendamento</small></label>
                    <div class="form-input">
                        <select name="status" class=":required">
                            {if $agendamento->getStatus() == 'AGENDADO'}
                                <option value="AGENDADO" selected="">AGENDADO</option>
                                <option value="CANCELADO">CANCELADO</option>
                            {else}
                                <option value="AGENDADO">AGENDADO</option>
                                <option value="CANCELADO" selected="">CANCELADO</option>    
                            {/if}
                        </select>
                    </div>
                </div>

                <div class="clearfix">
                    <button type="submit" id="submit">Confirmar</button>
                    <button onclick="location.href = '{$url.agendamento}'; return false;">Voltar</button>
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
                agendamento = new Crud('agendamento', '', '')
            });
                
           jQuery('#formAgendamento').submit(function(){

               servicos = new Array();
               $("input[type=checkbox][name='servicos[]']:checked").each(function(){
                    servicos.push($(this).val());
                });
                    
    {/literal}
              var dataString = $("input:text, select").serialize() + "&servicos=" + servicos + "&id=" + {$agendamento->getId()} + "";
    {literal}
              agendamento.update(dataString); 
                  
              return false;    
            });
                
    </script> 
{/literal}
<!-- DATATABLES END -->
{include file=$footer}