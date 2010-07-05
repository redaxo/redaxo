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
	
	// Addon-Konfiguration
	include dirname( __FILE__) . '/config.inc.php';

	// unistall ok
	$REX['ADDON']['install']['tinymce'] = 0;
	
	// REDAXO 3.2.3, 4.0.x, 4.1.x - Dateien in Ordner files/addons/ kopieren
	if (($rxa_tinymce['rexversion'] == '32') or ($rxa_tinymce['rexversion'] == '40') or ($rxa_tinymce['rexversion'] == '41'))
	{
		$addon_filesdir = $REX['MEDIAFOLDER'] . '/addons/' . $rxa_tinymce['name'];
		if (is_dir($addon_filesdir))
		{
			if(!rex_deleteDir($addon_filesdir, true))
			{
				$REX['ADDON']['installmsg'][$rxa_tinymce['name']] = 'Verzeichnis '.$addon_filesdir.' konnte nicht gelöscht werden!';
				$REX['ADDON']['install'][$rxa_tinymce['name']] = 1;	
			}
		}
	}
