<?php /* Smarty version Smarty-3.1.12, created on 2012-10-18 02:35:52
         compiled from "C:\xampp\htdocs\easyframework\demos\helloworld\App\View\Pages\Home\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:12989507f95384cde12-56081425%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c5c59b5e3643451cbd3dfbbf12c71b90b326f185' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\App\\View\\Pages\\Home\\index.tpl',
      1 => 1346216382,
      2 => 'file',
    ),
    '48914c14e4fb18f5e53935231734d981a0c73906' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\App\\View\\Layouts\\Layout.tpl',
      1 => 1345784308,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '12989507f95384cde12-56081425',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_507f95385495b7_89732860',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_507f95385495b7_89732860')) {function content_507f95385495b7_89732860($_smarty_tpl) {?><!DOCTYPE html>
<html lang="pt-BR"> 
    <head>
        <meta charset="utf-8">
        <title><?php echo __("Hello World Exemple");?>
</title>
    </head>
    <body>
        
<h1><?php echo $_smarty_tpl->tpl_vars['var']->value;?>
</h1>
<hr/>

<h3>Welcome to Easy Framework Demos</h3>
<p>Here you can see the folder structure, the default controller and model classes and many other things.</p>
<p>Enjoy it.</p>

<h4>Other Demos</h4>
<ul>
    <li><a href="#">Bookstore Manager</a> - A complete bookstore system, here you can learn how to implement complex operations with EasyFw.</li>
    <li><a href="#">Blog</a> - A complete .</li>
</ul>

    </body>
</html><?php }} ?>