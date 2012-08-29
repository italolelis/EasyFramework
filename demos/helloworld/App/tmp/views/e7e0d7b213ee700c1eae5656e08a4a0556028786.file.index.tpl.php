<?php /* Smarty version Smarty-3.1.8, created on 2012-05-14 02:11:13
         compiled from "C:\xampp\htdocs\easyframework\demos\helloworld\app\View\Pages\Home\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:51884fb044b9391736-45608764%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e7e0d7b213ee700c1eae5656e08a4a0556028786' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\app\\View\\Pages\\Home\\index.tpl',
      1 => 1336971656,
      2 => 'file',
    ),
    '8a23679ac89488230e9c466fbb23e6a76bb8e639' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\app\\View\\Layouts\\layout.tpl',
      1 => 1335159100,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '51884fb044b9391736-45608764',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.8',
  'unifunc' => 'content_4fb044b93e0207_96395453',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4fb044b93e0207_96395453')) {function content_4fb044b93e0207_96395453($_smarty_tpl) {?><!DOCTYPE html>
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