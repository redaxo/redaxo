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

	// CSS speichern
	if ($rxa_tinymce['get_func'] == 'update')
	{
		$rxa_tinymce['get_active'] = rex_request('active', 'string');
		$rxa_tinymce['get_lang'] = strtolower(rex_request('lang', 'string'));
		if ($rxa_tinymce['get_lang'] == '')
			$rxa_tinymce['get_lang'] = 'de';
		$rxa_tinymce['get_pages'] = strtolower(rex_request('pages', 'string'));
		if ($rxa_tinymce['get_pages'] == '')
			$rxa_tinymce['get_pages'] = 'content';
		$includepages = explode(',', trim(str_replace(' ', '', $rxa_tinymce['get_pages'])));
		if (!in_array('content', $includepages)) // Bei 'content' immer!
			$rxa_tinymce['get_pages'] = 'content, ' . $rxa_tinymce['get_pages'];
		$rxa_tinymce['get_foreground'] = rex_request('foreground', 'string');
		$rxa_tinymce['get_background'] = rex_request('background', 'string');
		$rxa_tinymce['get_validxhtml'] = rex_request('validxhtml', 'string');
		$rxa_tinymce['get_inlinepopups'] = rex_request('inlinepopups', 'string');
		$rxa_tinymce['get_theme'] = rex_request('theme', 'string');
		$rxa_tinymce['get_skin'] = rex_request('skin', 'string');
		$rxa_tinymce['get_extconfig'] = rex_request('extconfig', 'string');

		$REX['ADDON'][$rxa_tinymce['name']]['active'] = $rxa_tinymce['get_active'];
		$REX['ADDON'][$rxa_tinymce['name']]['lang'] = $rxa_tinymce['get_lang'];
		$REX['ADDON'][$rxa_tinymce['name']]['pages'] = $rxa_tinymce['get_pages'];
		$REX['ADDON'][$rxa_tinymce['name']]['foreground'] = $rxa_tinymce['get_foreground'];
		$REX['ADDON'][$rxa_tinymce['name']]['background'] = $rxa_tinymce['get_background'];
		$REX['ADDON'][$rxa_tinymce['name']]['validxhtml'] = $rxa_tinymce['get_validxhtml'];
		$REX['ADDON'][$rxa_tinymce['name']]['inlinepopups'] = $rxa_tinymce['get_inlinepopups'];
		$REX['ADDON'][$rxa_tinymce['name']]['theme'] = $rxa_tinymce['get_theme'];
		$REX['ADDON'][$rxa_tinymce['name']]['skin'] = $rxa_tinymce['get_skin'];
		$REX['ADDON'][$rxa_tinymce['name']]['extconfig'] = $rxa_tinymce['get_extconfig'];

		$rxa_tinymce['config_content'] = '
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'active\'] = \'' . $rxa_tinymce['get_active'] . '\';
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'lang\'] = \'' . $rxa_tinymce['get_lang'] . '\';
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'pages\'] = \'' . $rxa_tinymce['get_pages'] . '\';
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'foreground\'] = \'' . $rxa_tinymce['get_foreground'] . '\';
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'background\'] = \'' . $rxa_tinymce['get_background'] . '\';
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'validxhtml\'] = \'' . $rxa_tinymce['get_validxhtml'] . '\';
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'inlinepopups\'] = \'' . $rxa_tinymce['get_inlinepopups'] . '\';
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'theme\'] = \'' . $rxa_tinymce['get_theme'] . '\';
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'skin\'] = \'' . $rxa_tinymce['get_skin'] . '\';
$REX[\'ADDON\'][$rxa_tinymce[\'name\']][\'extconfig\'] = "
'. trim($rxa_tinymce['get_extconfig']) . '";
		';

		$filename = $REX['INCLUDE_PATH'] . '/addons/' . $rxa_tinymce['name'] . '/config.inc.php';
		if (rex_replace_dynamic_contents($filename, $rxa_tinymce['config_content']))
		{
			echo rex_info($I18N_A52->msg('msg_settings_saved'));
		}
		else		
		{
			echo rex_warning($I18N_A52->msg('msg_settings_error'));
		}
	}

	$rxa_tinymce['tinymce_langs'] = str_replace('.js', '', implode(',', a52_readFolderFiles($rxa_tinymce['fe_path'] . '/tiny_mce/langs')));
	
	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '<table border="0" cellpadding="5" cellspacing="1" width="770">';
		echo '<tr>';
		echo '<td class="grey">';
	}
?>

<div class="rex-addon-output">

<h2 class="rex-hl2"><?php echo $I18N_A52->msg('title_config'); ?></h2>

	<div class="rex-form">

	<form action="index.php" method="post">
		<fieldset class="rex-form-col-1">
      <div class="rex-form-wrapper">
				<input type="hidden" name="page" value="tinymce" />
				<input type="hidden" name="subpage" value="settings" />
				<input type="hidden" name="func" value="update" />

				<div class="rex-form-row">
					<p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
						<input class="rex-form-checkbox" type="checkbox" id="tinymce_active" name="active" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['active'] == 'on') echo 'checked="checked"'; ?> />
						<label for="tinymce_active"><?php echo $I18N_A52->msg('title_active'); ?></label>
					</p>
				</div>

				<div class="rex-form-row">
					<p class="rex-form-col-a rex-form-text">
						<label for="tinymce_lang"><?php echo $I18N_A52->msg('title_language'); ?></label>
						<input class="rex-form-text" type="text" id="tinymce_lang" name="lang" maxlength="2" value="<?php echo $REX['ADDON'][$rxa_tinymce['name']]['lang']; ?>" />
						<span class="rex-form-notice"><?php echo $I18N_A52->msg('tinymce_langs', $rxa_tinymce['tinymce_langs']); ?></span>
					</p>
				</div>
			
			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-text">
					<label for="tinymce_pages"><?php echo $I18N_A52->msg('title_pages'); ?></label>
					<input class="rex-form-text" type="text" id="tinymce_pages" name="pages" value="<?php echo $REX['ADDON'][$rxa_tinymce['name']]['pages']; ?>" />
					<span class="rex-form-notice"><?php echo $I18N_A52->msg('tinymce_pages'); ?></span>
				</p>
			</div>

			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-text">
					<label for="tinymce_foreground"><?php echo $I18N_A52->msg('title_foreground'); ?></label>
					<input class="rex-form-text" type="text" id="tinymce_foreground" name="foreground" value="<?php echo $REX['ADDON'][$rxa_tinymce['name']]['foreground']; ?>" />
					<span class="rex-form-notice"><?php echo $I18N_A52->msg('tinymce_foreground'); ?></span>
				</p>
			</div>

			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-text">
					<label for="tinymce_background"><?php echo $I18N_A52->msg('title_background'); ?></label>
					<input class="rex-form-text" type="text" id="tinymce_background" name="background" value="<?php echo $REX['ADDON'][$rxa_tinymce['name']]['background']; ?>" />
					<span class="rex-form-notice"><?php echo $I18N_A52->msg('tinymce_background'); ?></span>
				</p>
			</div>

			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
					<input class="rex-form-checkbox" type="checkbox" id="tinymce_validxhtml" name="validxhtml" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['validxhtml'] == 'on') echo 'checked="checked"'; ?> />
					<label for="tinymce_validxhtml"><?php echo $I18N_A52->msg('title_validxhtml'); ?></label>
					<span class="rex-form-notice"><?php echo $I18N_A52->msg('tinymce_validxhtml'); ?></span>
				</p>
			</div>

			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
					<input class="rex-form-checkbox" type="checkbox" id="tinymce_inlinepopups" name="inlinepopups" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['inlinepopups'] == 'on') echo 'checked="checked"'; ?> />
					<label for="tinymce_inlinepopups"><?php echo $I18N_A52->msg('title_inlinepopups'); ?></label>
					<span class="rex-form-notice"><?php echo $I18N_A52->msg('tinymce_inlinepopups'); ?></span>
				</p>
			</div>

			<div class="rex-form-row">
				<h5 class="rex-form-headline"><?php echo $I18N_A52->msg('title_theme'); ?></h5>
			</div>
			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-radio rex-form-label-right">
					<input class="rex-form-radio" type="radio" id="tinymce_theme_simple" name="theme" value="simple" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['theme'] == 'simple') echo 'checked="checked"'; ?>/>
					<label for="tinymce_theme_simple"><strong><?php echo $I18N_A52->msg('theme_simple'); ?></strong><br /><br /><img class="theme" src="./include/addons/tinymce/img/theme_simple.jpg" alt="" /></label>
				</p>
			</div>
			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-radio rex-form-label-right">
					<input class="rex-form-radio" type="radio" id="tinymce_theme_default" name="theme" value="default" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['theme'] == 'default') echo 'checked="checked"'; ?> />
					<label for="tinymce_theme_default"><strong><?php echo $I18N_A52->msg('theme_default'); ?></strong><br /><br /><img class="theme" src="./include/addons/tinymce/img/theme_default.jpg" alt="" /></label>
				</p>
			</div>
			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-radio rex-form-label-right">
					<input class="rex-form-radio" type="radio" id="tinymce_theme_advanced" name="theme" value="advanced" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['theme'] == 'advanced') echo 'checked="checked"'; ?> />
					<label for="tinymce_theme_advanced"><strong><?php echo $I18N_A52->msg('theme_advanced'); ?></strong><br /><br /><img class="theme" src="./include/addons/tinymce/img/theme_advanced.jpg" alt="" /></label>
				</p>
			</div>


			<div class="rex-form-row">
				<h5 class="rex-form-headline"><?php echo $I18N_A52->msg('title_skin'); ?></h5>
			</div>
			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-radio rex-form-label-right">
					<input class="rex-form-radio" type="radio" id="tinymce_skin_standard" name="skin" value="default" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['skin'] == 'default') echo 'checked="checked"'; ?>/>
					<label for="tinymce_skin_standard"><strong><?php echo $I18N_A52->msg('skin_standard'); ?></strong><br /><br /><img class="skin" src="./include/addons/tinymce/img/skin_default.jpg" alt="" /></label>
				</p>
			</div>
			<div class="rex-form-row">	
				<p class="rex-form-col-a rex-form-radio rex-form-label-right">			
					<input class="rex-form-radio" type="radio" id="tinymce_skin_o2k7" name="skin" value="o2k7" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['skin'] == 'o2k7') echo 'checked="checked"'; ?>/>
					<label for="tinymce_skin_o2k7"><strong><?php echo $I18N_A52->msg('skin_o2k7'); ?></strong><br /><br /><img class="skin" src="./include/addons/tinymce/img/skin_o2k7.jpg" alt="" /></label>
				</p>
			</div>
			<div class="rex-form-row">	
				<p class="rex-form-col-a rex-form-radio rex-form-label-right">
					<input class="rex-form-radio" type="radio" id="tinymce_skin_o2k7_silver" name="skin" value="o2k7_silver" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['skin'] == 'o2k7_silver') echo 'checked="checked"'; ?>/>
					<label for="tinymce_skin_o2k7_silver"><strong><?php echo $I18N_A52->msg('skin_o2k7_silver'); ?></strong><br /><br /><img class="skin" src="./include/addons/tinymce/img/skin_o2k7_silver.jpg" alt="" /></label>
				</p>
			</div>
			<div class="rex-form-row">	
				<p class="rex-form-col-a rex-form-radio rex-form-label-right">
					<input class="rex-form-radio" type="radio" id="tinymce_skin_o2k7_black" name="skin" value="o2k7_black" <?php if ($REX['ADDON'][$rxa_tinymce['name']]['skin'] == 'o2k7_black') echo 'checked="checked"'; ?>/>
					<label for="tinymce_skin_o2k7_black"><strong><?php echo $I18N_A52->msg('skin_o2k7_black'); ?></strong><br /><br /><img class="skin" src="./include/addons/tinymce/img/skin_o2k7_black.jpg" alt="" /></label>
				</p>
			</div>

			<div class="rex-form-row">
				<h5 class="rex-form-headline"><?php echo $I18N_A52->msg('title_ext_config'); ?></h5>
			</div>
			<div class="rex-form-row">	
				<p class="rex-form-textarea">
					<label for="extconfig"><?php echo $I18N_A52->msg('ext_config'); ?></label>
					<textarea class="rex-form-textarea" name="extconfig" id="extconfig" cols="80" rows="15"><?php echo htmlspecialchars(stripslashes($REX['ADDON'][$rxa_tinymce['name']]['extconfig'])); ?></textarea>
				</p>
			</div>
			<div class="rex-form-row">
				<p class="rex-form-col-a rex-form-submit">
					<input type="submit" class="rex-form-submit" name="sendit" value="<?php echo $I18N_A52->msg('button_save_settings'); ?>" />
				</p>
			</div>

		</div> <!-- END rex-form-wrapper -->
		</fieldset>
	</form>

	</div> <!-- END rex-form -->

</div> <!-- END rex-addon-output -->

<?php
	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
