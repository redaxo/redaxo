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

	unset($rxa_tinymce);
	$rxa_tinymce['name'] = 'tinymce';

	// Addon Settings
	$REX['ADDON']['rxid'][$rxa_tinymce['name']] = '52';
	$REX['ADDON']['name'][$rxa_tinymce['name']] = 'TinyMCE';
	$REX['ADDON']['perm'][$rxa_tinymce['name']] = 'tiny_mce[]';
	$REX['ADDON']['version'][$rxa_tinymce['name']] = '1.6';
	$REX['ADDON']['author'][$rxa_tinymce['name']] = 'Andreas Eberhard, Markus Staab, Dave Holloway';
	$REX['ADDON']['supportpage'][$rxa_tinymce['name']] = 'forum.redaxo.de';

	$REX['PERM'][] = 'tiny_mce[]';

	// REDAXO-Version
	$rxa_tinymce['rexversion'] = isset($REX['VERSION']) ? $REX['VERSION'] . $REX['SUBVERSION'] : '';

	// Versions-Spezifische Variablen/Konstanten
	$rxa_tinymce['medienpool'] = ($rxa_tinymce['rexversion'] > '41') ? 'mediapool' : 'medienpool';
	$rxa_tinymce['linkmap'] = 'linkmap';

	// Pfad für HTML-Ausgabe
	$rxa_tinymce['fe_path'] = $REX['HTDOCS_PATH'] . 'files/addons/' . $rxa_tinymce['name'];



// Konfigurationsvariablen, werden in pages/settings.inc.php geschrieben
// -----------------------------------------------------------------------------

// --- DYN
$REX['ADDON'][$rxa_tinymce['name']]['active'] = 'on';
$REX['ADDON'][$rxa_tinymce['name']]['lang'] = 'de';
$REX['ADDON'][$rxa_tinymce['name']]['pages'] = 'content, metainfo';
$REX['ADDON'][$rxa_tinymce['name']]['foreground'] = '';
$REX['ADDON'][$rxa_tinymce['name']]['background'] = '';
$REX['ADDON'][$rxa_tinymce['name']]['validxhtml'] = 'on';
$REX['ADDON'][$rxa_tinymce['name']]['inlinepopups'] = '';
$REX['ADDON'][$rxa_tinymce['name']]['theme'] = 'default';
$REX['ADDON'][$rxa_tinymce['name']]['skin'] = 'default';
$REX['ADDON'][$rxa_tinymce['name']]['extconfig'] = "
";
// --- /DYN
// -----------------------------------------------------------------------------



	// Nur im Backend
	if (isset($REX['REDAXO']) && $REX['REDAXO'])
	{
		// rexTinyMCEEditor-Klasse
		include_once $REX['INCLUDE_PATH'] . '/addons/' . $rxa_tinymce['name'] . '/classes/class.tinymce.inc.php';

		// Funktionen für TinyMCE
		include_once $REX['INCLUDE_PATH'] . '/addons/' . $rxa_tinymce['name'] . '/functions/function_rex_tinymce.inc.php';

		// Kompatibilitäts-Funktionen
		include_once $REX['INCLUDE_PATH'] . '/addons/' . $rxa_tinymce['name'] . '/functions/function_rex_compat.inc.php';

		// Request-Variablen
		$rxa_tinymce['get_page'] = rex_request('page', 'string');
		$rxa_tinymce['get_tinymce'] = rex_request('tinymce', 'string');
		$rxa_tinymce['get_tinymceinit'] = rex_request('tinymceinit', 'string');

		// TinyMCE-Init-Javascript ausliefern
		if ($rxa_tinymce['get_tinymceinit'] == 'true')
		{
			a52_tinymce_output_init();
			exit;
		}

		// Im Backend Sprachobjekt anlegen
		$I18N_A52 = new i18n($REX['LANG'], $REX['INCLUDE_PATH'] . '/addons/' . $rxa_tinymce['name'] . '/lang/');

		// Addon-Subnavigation für das REDAXO-Menue
		$REX['ADDON'][$rxa_tinymce['name']]['SUBPAGES'] = array (
			array('', $I18N_A52->msg('menu_module')),
			array('settings', $I18N_A52->msg('menu_settings')),
			array('css', $I18N_A52->msg('menu_css')),
			array('tipps', $I18N_A52->msg('menu_tipps')),
			array('info', $I18N_A52->msg('menu_information')),
		);

		// ausgewählte Seiten laut Konfiguration
		$rxa_tinymce['includepages'] = explode(',', trim(str_replace(' ', '', $REX['ADDON'][$rxa_tinymce['name']]['pages'])));
		if (!in_array('content', $rxa_tinymce['includepages'])) // Bei 'content' immer!
		{
			$rxa_tinymce['includepages'][] = 'content';
		}

		// TinyMCE ins Backend integrieren, nur in ausgewählten Seiten laut Konfiguration
		if(($rxa_tinymce['get_page'] <> '') and in_array($rxa_tinymce['get_page'], $rxa_tinymce['includepages']) and ($REX['ADDON'][$rxa_tinymce['name']]['active'] == 'on'))
		{
			rex_register_extension('OUTPUT_FILTER', 'a52_tinymce_opf');
		}

		// Outputfilter für Medienpool und Linkmap
		if ($REX['ADDON'][$rxa_tinymce['name']]['active'] == 'on') // nur wen TinyMCE aktiv
		{
			$rxa_tinymce['get_inputfield'] = rex_request('opener_input_field', 'string');
			if (strstr($rxa_tinymce['get_inputfield'], 'REX_MEDIA_') or strstr($rxa_tinymce['get_inputfield'], 'LINK_'))
			{
				$_SESSION['a52_tinymce'] = false;
			}		
			if ((($rxa_tinymce['get_page'] == $rxa_tinymce['medienpool']) or ($rxa_tinymce['get_page'] == $rxa_tinymce['linkmap'])) and (($rxa_tinymce['get_tinymce'] == 'true') or (isset($_SESSION['a52_tinymce']) and $_SESSION['a52_tinymce'] == 'true')))
			{
				rex_register_extension('MEDIA_ADDED', 'a52_tinymce_mediaadded');
				rex_register_extension('OUTPUT_FILTER', 'a52_tinymce_opf_media_linkmap');
			}
		}

	} // Ende nur im Backend
