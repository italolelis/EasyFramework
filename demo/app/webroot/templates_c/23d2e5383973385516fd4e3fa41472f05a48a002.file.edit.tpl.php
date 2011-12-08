<?php /* Smarty version Smarty-3.1.3, created on 2011-12-08 16:50:07
         compiled from "C:\xampp\htdocs\demo\app\view\usuarios\edit.tpl" */ ?>
<?php /*%%SmartyHeaderCode:192804ee0dc95967781-46585455%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '23d2e5383973385516fd4e3fa41472f05a48a002' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\view\\usuarios\\edit.tpl',
      1 => 1323359400,
      2 => 'file',
    ),
    'd6f90c1064dbbbb1629a2db8df9c05487bec77ba' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\layouts\\layout.tpl',
      1 => 1323358870,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '192804ee0dc95967781-46585455',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.3',
  'unifunc' => 'content_4ee0dc959cc3a',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4ee0dc959cc3a')) {function content_4ee0dc959cc3a($_smarty_tpl) {?><!DOCTYPE html>
<html lang="pt-BR"> 
    <head>
        <meta charset="utf-8">
        <title>Exemplo Hello World</title>
    </head>
    <body>
        
<form method="POST" action="<?php echo $_smarty_tpl->tpl_vars['url']->value['editarUsuario'];?>
<?php echo $_smarty_tpl->tpl_vars['usuario']->value->id;?>
">
    <label>Nome: </label><input type="text" name="username" value="<?php echo $_smarty_tpl->tpl_vars['usuario']->value->username;?>
"/>
    <button type="submit">Confirmar</button>
</form>

    </body>
</html><?php }} ?>