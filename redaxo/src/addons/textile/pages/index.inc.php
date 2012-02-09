<?php

/**
 * Textile Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

echo rex_view::title('Textile');


$mdl_help = '<?php rex_textile::showHelpOverview(); ?>';


$mdl_ex ='<?php
if(rex_addon::get("textile")->isAvailable())
{
  if(REX_IS_VALUE[1])
  {
    $textile = htmlspecialchars_decode(\'REX_VALUE[1]\');
    $textile = str_replace("<br />","",$textile);
    echo rex_textile::parse($textile);
  }
}
else
{
  echo rex_view::warning(\'Dieses Modul ben&ouml;tigt das "textile" Addon!\');
}
?>';

?>

<div class="rex-addon-output">
	<h2 class="rex-hl2"><?php echo rex_i18n::msg('textile_code_for_module_input'); ?></h2>

	<div class="rex-addon-content">
		<p class="rex-tx1"><?php echo rex_i18n::msg('textile_module_intro_help'); ?></p>
		<?php echo rex_string::highlight($mdl_help); ?>
		<p class="rex-tx1"><?php echo rex_i18n::msg('textile_module_rights'); ?></p>
	</div>
</div>

<div class="rex-addon-output">
	<h2 class="rex-hl2"><?php echo rex_i18n::msg('textile_code_for_module_output'); ?></h2>

	<div class="rex-addon-content">
		<p class="rex-tx1"><?php echo rex_i18n::msg('textile_module_intro_moduleoutput'); ?></p>

		<h3><?php echo rex_i18n::msg('textile_example_for'); ?> REX_VALUE[1]</h3>
		<?php echo rex_string::highlight($mdl_ex); ?>
	</div>
</div>

<?php
