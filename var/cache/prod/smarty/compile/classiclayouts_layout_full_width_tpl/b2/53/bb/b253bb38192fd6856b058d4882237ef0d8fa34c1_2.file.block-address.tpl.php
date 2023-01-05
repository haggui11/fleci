<?php
/* Smarty version 3.1.43, created on 2022-10-22 00:58:35
  from '/home/admin/sites/fleci/themes/classic/templates/customer/_partials/block-address.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.43',
  'unifunc' => 'content_6353241b5f63a8_86284771',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b253bb38192fd6856b058d4882237ef0d8fa34c1' => 
    array (
      0 => '/home/admin/sites/fleci/themes/classic/templates/customer/_partials/block-address.tpl',
      1 => 1656195269,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6353241b5f63a8_86284771 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_2513174636353241b5f1015_10179081', 'address_block_item');
?>

<?php }
/* {block 'address_block_item_actions'} */
class Block_19918100856353241b5f3253_36419321 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

      <div class="address-footer">
        <a href="<?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['url'][0], array( array('entity'=>'address','id'=>$_smarty_tpl->tpl_vars['address']->value['id']),$_smarty_tpl ) );?>
" data-link-action="edit-address">
          <i class="material-icons">&#xE254;</i>
          <span><?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['l'][0], array( array('s'=>'Update','d'=>'Shop.Theme.Actions'),$_smarty_tpl ) );?>
</span>
        </a>
        <a href="<?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['url'][0], array( array('entity'=>'address','id'=>$_smarty_tpl->tpl_vars['address']->value['id'],'params'=>array('delete'=>1,'token'=>$_smarty_tpl->tpl_vars['token']->value)),$_smarty_tpl ) );?>
" data-link-action="delete-address">
          <i class="material-icons">&#xE872;</i>
          <span><?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['l'][0], array( array('s'=>'Delete','d'=>'Shop.Theme.Actions'),$_smarty_tpl ) );?>
</span>
        </a>
      </div>
    <?php
}
}
/* {/block 'address_block_item_actions'} */
/* {block 'address_block_item'} */
class Block_2513174636353241b5f1015_10179081 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'address_block_item' => 
  array (
    0 => 'Block_2513174636353241b5f1015_10179081',
  ),
  'address_block_item_actions' => 
  array (
    0 => 'Block_19918100856353241b5f3253_36419321',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

  <article id="address-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value['id'], ENT_QUOTES, 'UTF-8');?>
" class="address" data-id-address="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value['id'], ENT_QUOTES, 'UTF-8');?>
">
    <div class="address-body">
      <h4><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value['alias'], ENT_QUOTES, 'UTF-8');?>
</h4>
      <address><?php echo $_smarty_tpl->tpl_vars['address']->value['formatted'];?>
</address>
            <?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['hook'][0], array( array('h'=>'displayAdditionalCustomerAddressFields','address'=>$_smarty_tpl->tpl_vars['address']->value),$_smarty_tpl ) );?>

    </div>

    <?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_19918100856353241b5f3253_36419321', 'address_block_item_actions', $this->tplIndex);
?>

  </article>
<?php
}
}
/* {/block 'address_block_item'} */
}
