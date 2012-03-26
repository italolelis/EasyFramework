<?php /* Smarty version Smarty-3.1.7, created on 2012-03-26 02:24:02
         compiled from "C:\xampp\htdocs\easyframework\demo\app\View\Pages\Index\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:153624f6ffd721e0823-48326686%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c57d6aedc43e0e1198065711229efbedddb4d146' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demo\\app\\View\\Pages\\Index\\index.tpl',
      1 => 1332739394,
      2 => 'file',
    ),
    '08efc0e1563d4d6bec6a53ecd020c8123992c431' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demo\\app\\View\\Layouts\\layout.tpl',
      1 => 1332739394,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '153624f6ffd721e0823-48326686',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f6ffd7222b16',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f6ffd7222b16')) {function content_4f6ffd7222b16($_smarty_tpl) {?><!DOCTYPE html>
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