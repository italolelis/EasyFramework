<?php /* Smarty version Smarty-3.1.3, created on 2011-12-08 16:41:50
         compiled from "C:\xampp\htdocs\demo\app\view\home\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:251064eac76ad4f5703-75840686%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0a1cdd623d14fd71c98df80d3d3dd473437b6d85' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\view\\home\\index.tpl',
      1 => 1323358907,
      2 => 'file',
    ),
    'd6f90c1064dbbbb1629a2db8df9c05487bec77ba' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\layouts\\layout.tpl',
      1 => 1323358870,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '251064eac76ad4f5703-75840686',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.3',
  'unifunc' => 'content_4eac76ad53bba',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4eac76ad53bba')) {function content_4eac76ad53bba($_smarty_tpl) {?><!DOCTYPE html>
<html lang="pt-BR"> 
    <head>
        <meta charset="utf-8">
        <title>Exemplo Hello World</title>
    </head>
    <body>
        
<h1><?php echo $_smarty_tpl->tpl_vars['var']->value;?>
</h1>

    </body>
</html><?php }} ?>