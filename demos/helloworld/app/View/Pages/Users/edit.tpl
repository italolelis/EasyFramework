{extends file=$layout}

{block name=content}
<h1>Edit User {$user->username}</h1>
<form method="POST" action="{$url.editUser}/{$user->id}">
    <label>Nome: </label><input type="text" name="username" value="{$user->username}"/>
    <button type="submit">Confirmar</button>
</form>
<hr/>
<button onclick="location.href='{$url.users}'">Back to users</button>
{/block}