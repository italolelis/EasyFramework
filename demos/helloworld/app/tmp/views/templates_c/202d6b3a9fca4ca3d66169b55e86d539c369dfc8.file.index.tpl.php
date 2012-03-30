<?php /* Smarty version Smarty-3.1.8, created on 2012-03-30 17:04:01
         compiled from "C:\xampp\htdocs\easyframework\demos\helloworld\app\View\Pages\Users\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:235964f6ffe03415969-98111507%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '202d6b3a9fca4ca3d66169b55e86d539c369dfc8' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\app\\View\\Pages\\Users\\index.tpl',
      1 => 1332739396,
      2 => 'file',
    ),
    '8a23679ac89488230e9c466fbb23e6a76bb8e639' => 
    array (
      0 => 'C:\\xampp\\htdocs\\easyframework\\demos\\helloworld\\app\\View\\Layouts\\layout.tpl',
      1 => 1332739396,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '235964f6ffe03415969-98111507',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.8',
  'unifunc' => 'content_4f6ffe0349d87',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f6ffe0349d87')) {function content_4f6ffe0349d87($_smarty_tpl) {?><!DOCTYPE html>
<html lang="pt-BR"> 
    <head>
        <meta charset="utf-8">
        <title>Exemplo Hello World</title>
    </head>
    <body>
        

<table border='1px'>
    <thead>
        <tr>
            <td>Id</td>
            <td>Username</td>
            <td>Admin</td>
            <td>Actions</td>
        </tr>
    </thead>  
    <tbody>
        <?php  $_smarty_tpl->tpl_vars['user'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['user']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['users']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['user']->key => $_smarty_tpl->tpl_vars['user']->value){
$_smarty_tpl->tpl_vars['user']->_loop = true;
?>
            <tr>
                <td><?php echo $_smarty_tpl->tpl_vars['user']->value->id;?>
</td>
                <td><?php echo $_smarty_tpl->tpl_vars['user']->value->username;?>
</td>
                <td><?php echo $_smarty_tpl->tpl_vars['user']->value->admin;?>
</td>
                <td><a href='<?php echo $_smarty_tpl->tpl_vars['url']->value['viewUser'];?>
/<?php echo $_smarty_tpl->tpl_vars['user']->value->id;?>
'>View </a>| <a href='<?php echo $_smarty_tpl->tpl_vars['url']->value['editUser'];?>
/<?php echo $_smarty_tpl->tpl_vars['user']->value->id;?>
'>Edit</a> | <a href='<?php echo $_smarty_tpl->tpl_vars['url']->value['deleteUser'];?>
/<?php echo $_smarty_tpl->tpl_vars['user']->value->id;?>
'><?php echo __('Delete');?>
</a></td>
            </tr>
        <?php } ?>
    </tbody>  
</table>

<hr/>
<button onclick="location.href='<?php echo $_smarty_tpl->tpl_vars['url']->value['base'];?>
'">Back to main menu</button>
<button onclick="location.href='<?php echo $_smarty_tpl->tpl_vars['url']->value['addUser'];?>
'">Add User</button>


    </body>
</html><?php }} ?>