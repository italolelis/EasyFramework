<?php /* Smarty version Smarty-3.1.7, created on 2012-03-05 02:02:51
         compiled from "C:\xampp\htdocs\demo\app\View\Pages\Users\add.tpl" */ ?>
<?php /*%%SmartyHeaderCode:25534f5448fb77c692-60361183%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e5fb46c2c1605eb6f7cfd8b28f1cb8ad57c3492a' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\View\\Pages\\Users\\add.tpl',
      1 => 1330897693,
      2 => 'file',
    ),
    '40b8b07bcbdf700e2a88dc43c829bb273bfda8cb' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\View\\Layouts\\layout.tpl',
      1 => 1330897680,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '25534f5448fb77c692-60361183',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f5448fb7e7f3',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f5448fb7e7f3')) {function content_4f5448fb7e7f3($_smarty_tpl) {?><!DOCTYPE html>
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