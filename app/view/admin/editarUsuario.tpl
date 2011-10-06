{include file=$header}
<header class="container_12 clearfix">
    <div class="grid_12">
        <h1>Editar Usuário</h1>
    </div>
</header>
<section class="container_12 clearfix">
    <div class="portlet grid_12">
        <header>
            <h2>Editar usuário</h2>
        </header>
        <section>
            <div id="info" class="message success"> 
                <h3>Sucesso!</h3> 
                <p></p> 
            </div>

            <form class="form has-validation">
                <div class="clearfix">
                    <label for="nome" class="form-label">Nome <em>*</em><small>Digite seu nome</small></label>
                    <div class="form-input"><input type="text" name="nome" class=":required" value="{$usuario->getNome()}"/></div>

                    <label for="email" class="form-label">E-mail <em>*</em><small>Seu endereço de e-mail</small></label>
                    <div class="form-input"><input type="text" name="email" class=":email :required" value="{$usuario->getEmail()}"/></div>

                    <label for="tel" class="form-label">Telefone<small>00-0000-0000</small></label>
                    <div class="form-input"><input type="text" name="tel" OnKeyPress="formatar('##-####-####', this)"s maxLength="13" value="{$usuario->getTel()}"/></div>

                    <label for="cel" class="form-label">Celular<small>00-0000-0000</small></label>
                    <div class="form-input"><input type="text" name="cel" OnKeyPress="formatar('##-####-####', this)" maxLength="13" value="{$usuario->getCel()}"/></div>
                </div>

                <div class="clearfix">
                    <label for="endereco" class="form-label">Endereço<small>Seu endereço</small></label>
                    <div class="form-input"><input type="text" name="endereco"  maxLength="255" value="{$usuario->getEndereco()}"/></div>

                    <label for="comp" class="form-label">Complemento<small>Complemento de seu endereço</small></label>
                    <div class="form-input"><input type="text" name="complemento"  maxLength="45" value="{$usuario->getComplemento()}"/></div>

                    <label for="bairro" class="form-label">Bairro<small>Seu bairro</small></label>
                    <div class="form-input"><input type="text" name="bairro"  maxLength="45" value="{$usuario->getBairro()}"/></div>

                    <label for="cidade" class="form-label">Cidade<small>Sua cidade</small></label>
                    <div class="form-input"><input type="text"name="cidade"  maxLength="45" value="{$usuario->getCidade()}"/></div>
                </div>

                <div class="clearfix">
                    <label for="username" class="form-label">Username <em>*</em><small>Digite seu login</small></label>
                    <div class="form-input"><input type="text" name="username" class=":required"  maxLength="100" value="{$usuario->getUsername()}"/></div>

                    <label for="username" class="form-label">Senha<small>Alterar senha</small></label>
                    <div class="form-input"><button onclick="location.href = '{$url.editarSenha}{$usuario->getId()}'; return false;">Alterar</button></div>
                </div>

                <div class="clearfix">
                    <label for="username" class="form-label">Grupo<small>Alterar grupo</small></label>
                    <div class="form-input">
                        <select name="admin">
                            {if $usuario->getAdmin() == '1'}
                                <option value="1" selected="">Administrador</option>
                                <option value="0">Usuário</option>
                            {else}
                                <option value="1">Administrador</option>
                                <option value="0"  selected="">Usuário</option>
                            {/if}
                        </select>
                    </div>
                </div>

                <div class="clearfix">
                    <button class="button" type="submit">Confirmar</button>
                    <button onclick="location.href = '{$url.usuarios}'; return false;">Voltar</button>
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
              alert($(this).serialize());
              var dataString = $(this).serialize() + "&id=" + {$usuario->getId()} + "";
    {literal}
              usuario.update(dataString); 
                  
              return false;    
            });
                
    </script> 
{/literal}
<!-- DATATABLES END -->
{include file=$footer}