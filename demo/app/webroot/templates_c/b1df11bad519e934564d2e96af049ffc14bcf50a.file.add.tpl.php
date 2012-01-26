<?php /* Smarty version Smarty-3.1.7, created on 2012-01-26 19:46:27
         compiled from "C:\xampp\htdocs\demo\app\view\users\add.tpl" */ ?>
<?php /*%%SmartyHeaderCode:157274f219f21283c54-41786345%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b1df11bad519e934564d2e96af049ffc14bcf50a' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\view\\users\\add.tpl',
      1 => 1327603585,
      2 => 'file',
    ),
    'd6f90c1064dbbbb1629a2db8df9c05487bec77ba' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\layouts\\layout.tpl',
      1 => 1327183506,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '157274f219f21283c54-41786345',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f219f212e1f8',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f219f212e1f8')) {function content_4f219f212e1f8($_smarty_tpl) {?><!DOCTYPE html>
<html lang="pt-BR"> 
    <head>
        <meta charset="utf-8">
        <title>Exemplo Hello World</title>
    </head>
    <body>
        
<h1>New User</h1>
<form method="POST" action="<?php echo $_smarty_tpl->tpl_vars['url']->value['addUser'];?>
">
    <label>Name: </label><input type="text" name="username" />
    <label>Password: </label><input type="text" name="password" />
    <button type="submit">Save</button>
</form>
<hr/>
<button onclick="location.href='<?php echo $_smarty_tpl->tpl_vars['url']->value['users'];?>
'">Back to users</button>

    </body>
</html><?php }} ?>