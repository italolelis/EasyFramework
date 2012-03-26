<?php /* Smarty version Smarty-3.1.7, created on 2012-03-26 02:26:24
         compiled from "C:\xampp\htdocs\easyframework\demos\helloworld\app\View\Pages\Index\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:221424f6ffe00a14c44-54613796%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0d81e218e0e4f81dcf16dbc5bcafcdd3f2541f39' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\app\\View\\Pages\\Index\\index.tpl',
      1 => 1332739394,
      2 => 'file',
    ),
    '8a23679ac89488230e9c466fbb23e6a76bb8e639' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\app\\View\\Layouts\\layout.tpl',
      1 => 1332739394,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '221424f6ffe00a14c44-54613796',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f6ffe00a645b',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f6ffe00a645b')) {function content_4f6ffe00a645b($_smarty_tpl) {?><!DOCTYPE html>
<html lang="pt-BR"> 
    <head>
        <meta charset="utf-8">
        <title>Exemplo Hello World</title>
    </head>
    <body>
        
<h1><?php echo $_smarty_tpl->tpl_vars['var']->value;?>
</h1>
<hr/>
<h3>Menu</h3>
<ul>
    <li><a href="<?php echo $_smarty_tpl->tpl_vars['url']->value['users'];?>
">See all Users</a></li>
</ul>



    </body>
</html><?php }} ?>