{extends file=$layout}

{block name=content}

<table border='1px'>
    <thead>
        <tr>
            <td>Id</td>
            <td>Username</td>
            <td>Admin</td>
            <td>Actions</td>
        </tr>
    </thead>  
    <tbody>
        {foreach $users as $user}
            <tr>
                <td>{$user->id}</td>
                <td>{$user->username}</td>
                <td>{$user->admin}</td>
                <td><a href='{$url.viewUser}/{$user->id}'>View </a>| <a href='{$url.editUser}/{$user->id}'>Edit</a> | <a href='{$url.deleteUser}/{$user->id}'>Delete</a></td>
            </tr>
        {/foreach}
    </tbody>  
</table>

<hr/>
<button onclick="location.href='{$url.base}'">Back to main menu</button>
<button onclick="location.href='{$url.addUser}'">Add User</button>

{/block}