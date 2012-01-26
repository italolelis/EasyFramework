<?php /* Smarty version Smarty-3.1.7, created on 2012-01-26 19:44:20
         compiled from "C:\xampp\htdocs\demo\app\view\users\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:204294f1b392e967b14-91740006%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1013d238609cfc13f0501100be2656bb8a8e0447' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\view\\users\\index.tpl',
      1 => 1327603458,
      2 => 'file',
    ),
    'd6f90c1064dbbbb1629a2db8df9c05487bec77ba' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\layouts\\layout.tpl',
      1 => 1327183506,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '204294f1b392e967b14-91740006',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f1b392e9f8f7',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f1b392e9f8f7')) {function content_4f1b392e9f8f7($_smarty_tpl) {?><!DOCTYPE html>
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
'>Delete</a></td>
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