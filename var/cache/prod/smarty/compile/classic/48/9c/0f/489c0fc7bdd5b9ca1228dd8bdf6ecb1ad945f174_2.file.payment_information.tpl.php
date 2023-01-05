<?php
/* Smarty version 3.1.43, created on 2022-10-22 00:58:33
  from '/home/admin/sites/fleci/modules/vrpayecommerce/views/templates/hook/payment_information.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.43',
  'unifunc' => 'content_635324191f6480_09492678',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '489c0fc7bdd5b9ca1228dd8bdf6ecb1ad945f174' => 
    array (
      0 => '/home/admin/sites/fleci/modules/vrpayecommerce/views/templates/hook/payment_information.tpl',
      1 => 1662851971,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_635324191f6480_09492678 (Smarty_Internal_Template $_smarty_tpl) {
?>
<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="payment-info-link" href="<?php echo htmlspecialchars(call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'escape' ][ 0 ], array( $_smarty_tpl->tpl_vars['paymentInformationUrl']->value,'html','UTF-8' )), ENT_QUOTES, 'UTF-8');?>
">
	<span class="link-item">
		<i class="material-icons">person_pin</i>
	    <?php ob_start();
echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['l'][0], array( array('s'=>'FRONTEND_MC_INFO','mod'=>'vrpayecommerce'),$_smarty_tpl ) );
$_prefixVariable1 = ob_get_clean();
if ($_prefixVariable1 == "FRONTEND_MC_INFO") {?>My Payment Information<?php } else {
echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['l'][0], array( array('s'=>'FRONTEND_MC_INFO','mod'=>'vrpayecommerce'),$_smarty_tpl ) );
}?>
	</span>
</a><?php }
}
