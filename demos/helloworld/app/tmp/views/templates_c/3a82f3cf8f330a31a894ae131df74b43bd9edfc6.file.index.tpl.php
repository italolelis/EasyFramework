<?php /* Smarty version Smarty-3.1.7, created on 2012-03-05 02:49:40
         compiled from "C:\xampp\htdocs\demo\app\View\Pages\Users\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:275224f5448ad392d31-76367282%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3a82f3cf8f330a31a894ae131df74b43bd9edfc6' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\View\\Pages\\Users\\index.tpl',
      1 => 1330926566,
      2 => 'file',
    ),
    '40b8b07bcbdf700e2a88dc43c829bb273bfda8cb' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\View\\Layouts\\layout.tpl',
      1 => 1330897680,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '275224f5448ad392d31-76367282',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f5448ad423d0',
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f5448ad423d0')) {function content_4f5448ad423d0($_smarty_tpl) {?><!DOCTYPE html>
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