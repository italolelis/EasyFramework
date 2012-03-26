{extends file=$layout}

{block name=content}
<h1>New User</h1>
<form method="POST" action="{$url.addUser}">
    <label>Name: </label><input type="text" name="username" />
    <label>Password: </label><input type="password" name="password" />
    <button type="submit">Save</button>
</form>
<hr/>
<button onclick="location.href='{$url.users}'">Back to users</button>
{/block}