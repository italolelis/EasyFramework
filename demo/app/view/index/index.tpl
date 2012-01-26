{extends file=$layout}

{block name=content}
<h1>{$var}</h1>
<hr/>
<h3>Menu</h3>
<ul>
    <li><a href="{$url.users}">See all Users</a></li>
</ul>


{/block}