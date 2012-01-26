<?php /* Smarty version Smarty-3.1.7, created on 2012-01-26 19:47:28
         compiled from "C:\xampp\htdocs\demo\app\view\users\edit.tpl" */ ?>
<?php /*%%SmartyHeaderCode:172294f219ab1a91ec0-05857298%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd3528db65d35b7fda1ea4d66c63adc82478d9cf4' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\view\\users\\edit.tpl',
      1 => 1327603644,
      2 => 'file',
    ),
    'd6f90c1064dbbbb1629a2db8df9c05487bec77ba' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\layouts\\layout.tpl',
      1 => 1327183506,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '172294f219ab1a91ec0-05857298',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f219ab1b15bc',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f219ab1b15bc')) {function content_4f219ab1b15bc($_smarty_tpl) {?><!DOCTYPE html>
<html lang="pt-BR"> 
    <head>
        <meta charset="utf-8">
        <title>Exemplo Hello World</title>
    </head>
    <body>
        
<h1>Edit User <?php echo $_smarty_tpl->tpl_vars['user']->value->username;?>
</h1>
<form method="POST" action="<?php echo $_smarty_tpl->tpl_vars['url']->value['editUser'];?>
/<?php echo $_smarty_tpl->tpl_vars['user']->value->id;?>
">
    <label>Nome: </label><input type="text" name="username" value="<?php echo $_smarty_tpl->tpl_vars['user']->value->username;?>
"/>
    <button type="submit">Confirmar</button>
</form>
<hr/>
<button onclick="location.href='<?php echo $_smarty_tpl->tpl_vars['url']->value['users'];?>
'">Back to users</button>

    </body>
</html><?php }} ?>