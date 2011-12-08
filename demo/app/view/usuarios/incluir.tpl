{extends file=$layout}

{block name=content}
<form method="POST" action="{$url.incluirUsuario}">
    <label>Nome: </label><input type="text" name="username" />
    <label>Senha: </label><input type="text" name="password" />
    <button type="submit">Confirmar</button>
</form>
{/block}