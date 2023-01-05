<?php
/* Smarty version 3.1.43, created on 2022-10-22 00:57:55
  from '/home/admin/sites/fleci/themes/classic/templates/_partials/helpers.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.43',
  'unifunc' => 'content_635323f3333887_62697263',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9f86515f1a45a3722791da65328efb43fe6a6f65' => 
    array (
      0 => '/home/admin/sites/fleci/themes/classic/templates/_partials/helpers.tpl',
      1 => 1656195269,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_635323f3333887_62697263 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->smarty->ext->_tplFunction->registerTplFunctions($_smarty_tpl, array (
  'renderLogo' => 
  array (
    'compiled_filepath' => '/home/admin/sites/fleci/var/cache/prod/smarty/compile/classiclayouts_layout_left_column_tpl/9f/86/51/9f86515f1a45a3722791da65328efb43fe6a6f65_2.file.helpers.tpl.php',
    'uid' => '9f86515f1a45a3722791da65328efb43fe6a6f65',
    'call_name' => 'smarty_template_function_renderLogo_1159303078635323f33306d3_60245789',
  ),
));
?> 

<?php }
/* smarty_template_function_renderLogo_1159303078635323f33306d3_60245789 */
if (!function_exists('smarty_template_function_renderLogo_1159303078635323f33306d3_60245789')) {
function smarty_template_function_renderLogo_1159303078635323f33306d3_60245789(Smarty_Internal_Template $_smarty_tpl,$params) {
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value, $_smarty_tpl->isRenderingCache);
}
?>

  <a href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['urls']->value['pages']['index'], ENT_QUOTES, 'UTF-8');?>
">
    <img
      class="logo img-fluid"
      src="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['shop']->value['logo_details']['src'], ENT_QUOTES, 'UTF-8');?>
"
      alt="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['shop']->value['name'], ENT_QUOTES, 'UTF-8');?>
"
      width="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['shop']->value['logo_details']['width'], ENT_QUOTES, 'UTF-8');?>
"
      height="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['shop']->value['logo_details']['height'], ENT_QUOTES, 'UTF-8');?>
">
  </a>
<?php
}}
/*/ smarty_template_function_renderLogo_1159303078635323f33306d3_60245789 */
}
