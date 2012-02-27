<?php /* Smarty version Smarty-3.1.7, created on 2012-02-25 19:09:23
         compiled from "C:\xampp\htdocs\demo\app\view\Users\add.tpl" */ ?>
<?php /*%%SmartyHeaderCode:80284f4923556182f8-41086268%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '256d6f77952d8ca80babf50e5b3c79eae8335986' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\view\\Users\\add.tpl',
      1 => 1330193313,
      2 => 'file',
    ),
    'd6f90c1064dbbbb1629a2db8df9c05487bec77ba' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\layouts\\layout.tpl',
      1 => 1327183506,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '80284f4923556182f8-41086268',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f4923556768e',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f4923556768e')) {function content_4f4923556768e($_smarty_tpl) {?><!DOCTYPE html>
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
    <label>Password: </label><input type="password" name="password" />
    <button type="submit">Save</button>
</form>
<hr/>
<button onclick="location.href='<?php echo $_smarty_tpl->tpl_vars['url']->value['users'];?>
'">Back to users</button>

    </body>
</html><?php }} ?>