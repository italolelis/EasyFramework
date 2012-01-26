<?php /* Smarty version Smarty-3.1.7, created on 2012-01-26 19:43:53
         compiled from "C:\xampp\htdocs\demo\app\view\Index\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:169604f1b38e22ff296-36045949%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1c83d9c30e05d6ce4416311e41098d9d4ad93f91' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\view\\Index\\index.tpl',
      1 => 1327603430,
      2 => 'file',
    ),
    'd6f90c1064dbbbb1629a2db8df9c05487bec77ba' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\layouts\\layout.tpl',
      1 => 1327183506,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '169604f1b38e22ff296-36045949',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f1b38e236ac6',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f1b38e236ac6')) {function content_4f1b38e236ac6($_smarty_tpl) {?><!DOCTYPE html>
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