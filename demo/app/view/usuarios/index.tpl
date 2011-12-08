{extends file=$layout}

{block name=content}
<ul>
    {foreach $usuarios as $usuario}
        <li>{$usuario->username} <a href="{$url.editarUsuario}{$usuario->id}">Editar</a> || <a href="{$url.excluirUsuario}{$usuario->id}">Excluir</a></li>
    {/foreach}
</ul>
<hr/>
<a href="{$url.incluirUsuario}">Incluir</a>
{/block}