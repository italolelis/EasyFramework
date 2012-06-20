<?php /* Smarty version Smarty-3.1.8, created on 2012-05-17 03:18:47
         compiled from "C:\xampp\htdocs\easyframework\demos\helloworld\app\View\Pages\Home\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:66184fb49847add099-02657793%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '29c995a9aec4f13b7a284ae214901dde7608a87a' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\app\\View\\Pages\\Home\\index.tpl',
      1 => 1336971656,
      2 => 'file',
    ),
    'd354cc18cc3de717bd6ed5219f9fba16c12f3198' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\app\\View\\Layouts\\Layout.tpl',
      1 => 1335159100,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '66184fb49847add099-02657793',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.8',
  'unifunc' => 'content_4fb49847b2ffe7_84331416',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4fb49847b2ffe7_84331416')) {function content_4fb49847b2ffe7_84331416($_smarty_tpl) {?><!DOCTYPE html>
<html lang="pt-BR"> 
    <head>
        <meta charset="utf-8">
        <title>Exemplo Hello World</title>
    </head>
    <body>
        
<h1><?php echo $_smarty_tpl->tpl_vars['var']->value;?>
</h1>
<hr/>

<h3>Welcome to the Easy Framework Demos</h3>
<p>Here you can see the folder structure, the default controller and model classes and many other things.</p>
<p>Enjoy it.</p>

<h4>Other Demos</h4>
<ul>
    <li><a href="#">Bookstore Manager</a> - A complete bookstore system, here you can learn how to implement a complex operations with EasyFw.</li>
    <li><a href="#">Blog</a> - A complete .</li>
</ul>

    </body>
</html><?php }} ?>