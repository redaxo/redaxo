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

	<h2 class="rex-hl2"><?php echo $I18N_A52->msg('title_tinymcelinks'); ?></h2>
	<div class="rex-addon-content">
		<p class="rex-tx1">
<?php
	$file = dirname( __FILE__) . '/../lang/help.' . $REX['LANG'] . '.lang';
	if(is_readable($file))
	{
		if (strstr($REX['LANG'], 'utf8'))
		{
			echo nl2br(utf8_encode(file_get_contents($file)));
		}
		else
		{
			echo nl2br(file_get_contents($file));
		}
	}
?>
		</p>
	</div>
</div>

<div class="rex-addon-output">
	<h2 class="rex-hl2"><?php echo $I18N_A52->msg('title_changelog'); ?></h2>
	<div class="rex-addon-content">
<?php
	$file = dirname( __FILE__) .'/../_changelog.txt';
	if(is_readable($file))
	{
		if (strstr($REX['LANG'], 'utf8'))
		{
			echo nl2br(utf8_encode(file_get_contents($file)));
		}
		else
		{
			echo nl2br(file_get_contents($file));
		}
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
