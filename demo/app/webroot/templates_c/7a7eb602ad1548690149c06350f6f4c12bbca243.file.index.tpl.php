<?php /* Smarty version Smarty-3.1.3, created on 2011-12-08 16:43:27
         compiled from "C:\xampp\htdocs\demo\app\view\usuarios\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:212854ee0dac76f80e2-05981490%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7a7eb602ad1548690149c06350f6f4c12bbca243' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\view\\usuarios\\index.tpl',
      1 => 1323359004,
      2 => 'file',
    ),
    'd6f90c1064dbbbb1629a2db8df9c05487bec77ba' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\layouts\\layout.tpl',
      1 => 1323358870,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '212854ee0dac76f80e2-05981490',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.3',
  'unifunc' => 'content_4ee0dac778187',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4ee0dac778187')) {function content_4ee0dac778187($_smarty_tpl) {?><!DOCTYPE html>
<html lang="pt-BR"> 
    <head>
        <meta charset="utf-8">
        <title>Exemplo Hello World</title>
    </head>
    <body>
        
<ul>
    <?php  $_smarty_tpl->tpl_vars['usuario'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['usuario']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['usuarios']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['usuario']->key => $_smarty_tpl->tpl_vars['usuario']->value){
$_smarty_tpl->tpl_vars['usuario']->_loop = true;
?>
        <li><?php echo $_smarty_tpl->tpl_vars['usuario']->value->username;?>
 <a href="<?php echo $_smarty_tpl->tpl_vars['url']->value['editarUsuario'];?>
<?php echo $_smarty_tpl->tpl_vars['usuario']->value->id;?>
">Editar</a> || <a href="<?php echo $_smarty_tpl->tpl_vars['url']->value['excluirUsuario'];?>
<?php echo $_smarty_tpl->tpl_vars['usuario']->value->id;?>
">Excluir</a></li>
    <?php } ?>
</ul>
<hr/>
<a href="<?php echo $_smarty_tpl->tpl_vars['url']->value['incluirUsuario'];?>
">Incluir</a>

    </body>
</html><?php }} ?>