<?php /* Smarty version Smarty-3.1.7, created on 2012-03-05 02:02:48
         compiled from "C:\xampp\htdocs\demo\app\View\Pages\Index\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:312994f5448f89ba275-36798880%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '67a94b9d1c079d72cbd0f6e8f9841172728a0ed1' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\View\\Pages\\Index\\index.tpl',
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
  'nocache_hash' => '312994f5448f89ba275-36798880',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f5448f8a1661',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f5448f8a1661')) {function content_4f5448f8a1661($_smarty_tpl) {?><!DOCTYPE html>
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