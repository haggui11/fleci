<?php
/* Smarty version 3.1.43, created on 2022-11-03 22:34:18
  from '/home/admin/sites/fleci/admin_ps/themes/default/template/content.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.43',
  'unifunc' => 'content_636433da8305f3_06010351',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '23799a298ad6389ad7ab78729a8ca310ad87b4b2' => 
    array (
      0 => '/home/admin/sites/fleci/admin_ps/themes/default/template/content.tpl',
      1 => 1656195267,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_636433da8305f3_06010351 (Smarty_Internal_Template $_smarty_tpl) {
?><div id="ajax_confirmation" class="alert alert-success hide"></div>
<div id="ajaxBox" style="display:none"></div>

<div class="row">
	<div class="col-lg-12">
		<?php if ((isset($_smarty_tpl->tpl_vars['content']->value))) {?>
			<?php echo $_smarty_tpl->tpl_vars['content']->value;?>

		<?php }?>
	</div>
</div>
<?php }
}
