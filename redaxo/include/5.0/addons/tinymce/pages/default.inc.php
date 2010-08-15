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

	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '<table border="0" cellpadding="5" cellspacing="1" width="770">';
		echo '<tr>';
		echo '<td class="grey">';
	}
?>

<div class="rex-addon-output">

	<h2 class="rex-hl2"><?php echo $I18N_A52->msg('title_simple_sample'); ?></h2>
	<div class="rex-addon-content">

	<p>
		<?php echo $I18N_A52->msg('txt_simple_sample'); ?>
	</p>

<?php
	echo '<p>';
	echo $I18N_A52->msg('title_input');
	$filename = dirname( __FILE__) . '/../examples/simple-input.txt';
	if(is_readable($filename))
	{
		echo '<textarea class="tinymce-code-small" onfocus="this.select();" style="width:98%;height:125px;">';
		if (strstr($REX['LANG'],'utf8'))
		{
			echo utf8_encode(htmlspecialchars(file_get_contents($filename)));
		}
		else
		{
			echo htmlspecialchars(file_get_contents($filename));
		}
		echo '</textarea></p>';
	}

	echo '<p>';
	echo $I18N_A52->msg('title_output');
	$filename = dirname( __FILE__) . '/../examples/output.txt';
	if(is_readable($filename))
	{
		echo '<textarea class="tinymce-code-small" onfocus="this.select();" style="width:98%;height:125px;">';
		if (strstr($REX['LANG'],'utf8'))
		{
			echo utf8_encode(htmlspecialchars(file_get_contents($filename)));
		}
		else
		{
			echo htmlspecialchars(file_get_contents($filename));
		}
		echo '</textarea></p>';
	}
?>
	</div>
</div>

<div class="rex-addon-output">

	<h2 class="rex-hl2"><?php echo $I18N_A52->msg('title_extended_sample'); ?></h2>
	<div class="rex-addon-content">

	<p>
		<?php echo $I18N_A52->msg('txt_extended_sample'); ?>
	</p>

<?php
	echo '<p>';
	echo $I18N_A52->msg('title_input');
	$filename = dirname( __FILE__) . '/../examples/extended-input.txt';
	if(is_readable($filename))
	{
		echo '<textarea class="tinymce-code-big" onfocus="this.select();" style="width:98%;height:300px;">';
		if (strstr($REX['LANG'],'utf8'))
		{
			echo utf8_encode(htmlspecialchars(file_get_contents($filename)));
		}
		else
		{
			echo htmlspecialchars(file_get_contents($filename));
		}
		echo '</textarea></p>';
	}
?>

	</div>
</div>

<?php
	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
