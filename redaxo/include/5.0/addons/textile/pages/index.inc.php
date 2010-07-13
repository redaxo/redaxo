<?php

/**
 * Textile Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

require $REX['INCLUDE_PATH'].'/layout/top.php';

rex_title('Textile');


$mdl_help = '<?php rex_a79_help_overview(); ?>';


$mdl_ex ='<?php
if(OOAddon::isAvailable("textile"))
{
  if(REX_IS_VALUE[1])
  {
    $textile = htmlspecialchars_decode(\'REX_VALUE[1]\');
    $textile = str_replace("<br />","",$textile);
    echo rex_a79_textile($textile);
  }
}
else
{
  echo rex_warning(\'Dieses Modul ben&ouml;tigt das "textile" Addon!\');
}
?>';

?>

<div class="rex-addon-output">
	<h2 class="rex-hl2"><?php echo $I18N->msg('textile_code_for_module_input'); ?></h2>

	<div class="rex-addon-content">
		<p class="rex-tx1"><?php echo $I18N->msg('textile_module_intro_help'); ?></p>
		<?php rex_highlight_string($mdl_help); ?>
		<p class="rex-tx1"><?php echo $I18N->msg('textile_module_rights'); ?></p>
	</div>
</div>

<div class="rex-addon-output">
	<h2 class="rex-hl2"><?php echo $I18N->msg('textile_code_for_module_output'); ?></h2>

	<div class="rex-addon-content">
		<p class="rex-tx1"><?php echo $I18N->msg('textile_module_intro_moduleoutput'); ?></p>

		<h3><?php echo $I18N->msg('textile_example_for'); ?> REX_VALUE[1]</h3>
		<?php rex_highlight_string($mdl_ex); ?>
	</div>
</div>

<?php
require $REX['INCLUDE_PATH'].'/layout/bottom.php';