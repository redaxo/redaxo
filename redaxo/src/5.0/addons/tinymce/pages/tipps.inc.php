<?php
/**
 * TinyMCE Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @author andreas[dot]eberhard[at]redaxo[dot]de Andreas Eberhard
 * @author <a href="http://rex.andreaseberhard.de">rex.andreaseberhad.de</a>
 *
 * @author Dave Holloway
 * @author <a href="http://www.GN2-Netwerk.de">www.GN2-Netwerk.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$tinymce_tipp1 = '$mytinyconfig =<<<EOD
   plugins : \'advhr,advimage,advlink,contextmenu, ... ,syntaxhighlighter,table,template\',
   skin : \'o2k7\',
   skin_variant : \'silver\'
EOD;
$tiny->configuration = $mytinyconfig;';

$tinymce_tipp2 = '<script type="text/javascript"
src="files/addons/tinymce/tiny_mce/plugins/media/js/embed.js"></script>';
$tinymce_tipp3 = '<script type="text/javascript"
src="redaxo/include/addons/tinymce/tinymce/jscripts/tiny_mce/plugins/media/js/embed.js"></script>';

$tinymce_tipp4 = '';

	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '<table border="0" cellpadding="5" cellspacing="1" width="770">';
		echo '<tr>';
		echo '<td class="grey">';
	}
?>

<div class="rex-addon-output">

	<h2 class="rex-hl2"><?php echo $I18N_A52->msg('title_tipps_class'); ?></h2>
	<div class="rex-addon-content">

<?php
	echo '<p class="form-text">';
	echo $I18N_A52->msg('tipps_class_01');
	echo $I18N_A52->msg('tipps_class_02');
	echo $I18N_A52->msg('tipps_class_03');
	echo '</p>';
	rex_highlight_string($tinymce_tipp1);
	echo '<p class="form-text">';
	echo $I18N_A52->msg('tipps_class_04');
	echo $I18N_A52->msg('tipps_class_05');
	echo $I18N_A52->msg('tipps_class_06');
	echo $I18N_A52->msg('tipps_class_07');
	echo '</p>';
?>
	</div>

</div>

<div class="rex-addon-output">

	<h2 class="rex-hl2"><?php echo $I18N_A52->msg('title_tipps_media'); ?></h2>
	<div class="rex-addon-content">
<?php
	echo '<p class="form-text">';
	echo $I18N_A52->msg('tipps_media_01');
	echo $I18N_A52->msg('tipps_media_02');
	echo $I18N_A52->msg('tipps_media_03');
	echo '</p>';
	rex_highlight_string($tinymce_tipp2);
	echo '<p class="form-text">';
	echo $I18N_A52->msg('tipps_media_04');
	echo '</p>';
	rex_highlight_string($tinymce_tipp3);
?>
	</div>

</div>

<!--
<div class="rex-addon-output">

	<h2 class="rex-hl2"><?php echo $I18N_A52->msg('title_tipps_syntax'); ?></h2>
	<div class="rex-addon-content">
<?php
	echo '<p class="form-text">';
	echo $I18N_A52->msg('tipps_syntax_01');
	echo $I18N_A52->msg('tipps_syntax_02');
	echo $I18N_A52->msg('tipps_syntax_03');
	echo '</p>';
	//rex_highlight_string($tinymce_tipp4);
?>
	</div>

</div>
-->

<?php
	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
