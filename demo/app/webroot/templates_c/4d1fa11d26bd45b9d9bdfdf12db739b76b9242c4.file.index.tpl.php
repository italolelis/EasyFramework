<?php /* Smarty version Smarty-3.1.7, created on 2012-02-25 19:04:16
         compiled from "C:\xampp\htdocs\demo\app\view\Users\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:148944f4922a0c26799-17384462%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4d1fa11d26bd45b9d9bdfdf12db739b76b9242c4' => 
    array (
      0 => 'C:\\xampp\\htdocs\\demo\\app\\view\\Users\\index.tpl',
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
  'nocache_hash' => '148944f4922a0c26799-17384462',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'layout' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_4f4922a0cc284',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_4f4922a0cc284')) {function content_4f4922a0cc284($_smarty_tpl) {?><!DOCTYPE html>
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