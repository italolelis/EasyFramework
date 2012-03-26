<?php /* Smarty version Smarty-3.1.7, created on 2012-03-05 02:02:54
         compiled from "C:\xampp\htdocs\demo\app\View\Pages\Users\edit.tpl" */ ?>
<?php /*%%SmartyHeaderCode:306234f5448fedb0b69-80007021%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '89175643c692ae34d2a067d21e2e3be881d1c27b' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\View\\Pages\\Users\\edit.tpl',
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
  'nocache_hash' => '306234f5448fedb0b69-80007021',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f5448fee154d',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f5448fee154d')) {function content_4f5448fee154d($_smarty_tpl) {?><!DOCTYPE html>
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