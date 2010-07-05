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

	$rxa_tinymce['get_func'] = rex_request('func', 'string');
	$rxa_tinymce['get_tinymcecss'] = rex_request('tinymcecss', 'string');

	$filename = $rxa_tinymce['fe_path'] . '/content.css';
	
	// CSS speichern
	if ($rxa_tinymce['get_func'] == 'update')
	{
		@chmod($filename, 0755);

		$rxa_tinymce['get_tinymcecss'] = stripslashes($rxa_tinymce['get_tinymcecss']);
		if (file_put_contents($filename, $rxa_tinymce['get_tinymcecss']))
		{
			echo rex_info($I18N_A52->msg('msg_css_saved'));
		}
		else
		{
			echo rex_warning($I18N_A52->msg('msg_css_error'));
		}
	}

	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '<table border="0" cellpadding="5" cellspacing="1" width="770">';
		echo '<tr>';
		echo '<td class="grey">';
	}
?>

<div class="rex-addon-output">

	<h2 class="rex-hl2"><?php echo $I18N_A52->msg('title_css_wysiwyg'); ?><br />[ <?php echo $filename; ?> ]</h2>

	<div class="rex-area">
	<div class="rex-form">
	
		<form action="index.php" method="post">
		<fieldset>
		<div class="rex-form-wrapper">
		<input type="hidden" name="page" value="tinymce" />
		<input type="hidden" name="subpage" value="css" />
		<input type="hidden" name="func" value="update" />
<?php
	if(is_readable($filename))
	{
		$csstext = htmlspecialchars(file_get_contents($filename));
	}
?>
		<div class="rex-form-row">
			<p class="rex-form-textarea">
				<label for="tinymcecss">CSS</label>
				<textarea class="rex-form-textarea" name="tinymcecss" id="tinymcecss" cols="80" rows="20"><?php echo htmlspecialchars($csstext); ?></textarea>
			</p>
		</div>

		<div class="rex-form-row">
			<p class="rex-form-submit">
				<input class="rex-form-submit" type="submit" value="<?php echo $I18N_A52->msg('button_save_css'); ?>" />
			</p>
		</div>

		</div>
		</fieldset>
		</form>

	</div> <!-- END rex-form -->
	</div> <!-- END rex-area -->

</div> <!-- END rex-addon-output -->

<?php
	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
