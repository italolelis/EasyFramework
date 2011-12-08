{extends file=$layout}

{block name=content}
<form method="POST" action="{$url.editarUsuario}{$usuario->id}">
    <label>Nome: </label><input type="text" name="username" value="{$usuario->username}"/>
    <button type="submit">Confirmar</button>
</form>
{/block}