<?php
/* Smarty version 3.1.43, created on 2022-10-22 18:40:54
  from '/home/admin/sites/fleci/themes/classic/templates/catalog/_partials/product-additional-info.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.43',
  'unifunc' => 'content_63541d169ea889_56286006',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '06583acc12860e95f52c10258ea5a0734e1c3bb2' => 
    array (
      0 => '/home/admin/sites/fleci/themes/classic/templates/catalog/_partials/product-additional-info.tpl',
      1 => 1656195269,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_63541d169ea889_56286006 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="product-additional-info js-product-additional-info">
  <?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['hook'][0], array( array('h'=>'displayProductAdditionalInfo','product'=>$_smarty_tpl->tpl_vars['product']->value),$_smarty_tpl ) );?>

</div>
<?php }
}
