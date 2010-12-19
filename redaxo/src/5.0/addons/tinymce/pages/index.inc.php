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

	require $REX['INCLUDE_PATH'] . '/layout/top.php';

	// Addon-Subnavigation
	$subpages = array(
		array('', $I18N_A52->msg('menu_module')),
		array('settings', $I18N_A52->msg('menu_settings')),
		array('css', $I18N_A52->msg('menu_css')),
		array('tipps', $I18N_A52->msg('menu_tipps')),
		array('info', $I18N_A52->msg('menu_information')),
	);

	// Titel
	rex_title($I18N_A52->msg('title'), $subpages);

	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '<table border="0" cellpadding="0" cellspacing="0" width="770">';
		echo '<tr>';
		echo '<td>';
	}

	// Include der angeforderten Seite
	$subpage = rex_request('subpage', 'string');

	switch($subpage) {
		case 'settings':
			include (dirname( __FILE__).'/settings.inc.php');
		break;
		case 'css':
			include (dirname( __FILE__).'/css.inc.php');
		break;
		case 'tipps':
			include (dirname( __FILE__).'/tipps.inc.php');
		break;
		case 'info':
			include (dirname( __FILE__).'/info.inc.php');
		break;
		default:
			include (dirname( __FILE__).'/default.inc.php');
		break;
	}

	// Tabelle bei REDAXO 3.2.x ausgeben
	if ($rxa_tinymce['rexversion'] == '32')
	{
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}

	require $REX['INCLUDE_PATH'] .'/layout/bottom.php';

	return;
