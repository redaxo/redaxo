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

	// Schreibberechtigung für Konfigurationsetzen
	@chmod(dirname( __FILE__) . '/config.inc.php', 0755);

	// Install ok
	$REX['ADDON']['install'][$rxa_tinymce['name']] = 1;
	
	// REDAXO 3.2.3, 4.0.x, 4.1.x - Dateien in Ordner files/addons/ kopieren
	if (($rxa_tinymce['rexversion'] == '32') or ($rxa_tinymce['rexversion'] == '40') or ($rxa_tinymce['rexversion'] == '41'))
	{
		$source_dir = $REX['INCLUDE_PATH'] . '/addons/' . $rxa_tinymce['name'] . '/files';
		$dest_dir = $REX['MEDIAFOLDER'] . '/addons/' . $rxa_tinymce['name'];
		$start_dir = $REX['MEDIAFOLDER'] . '/addons';
		
		if (is_dir($source_dir))
		{
			if (!is_dir($start_dir))
			{
				mkdir($start_dir);
			}
			if(!rex_copyDir($source_dir, $dest_dir , $start_dir))
			{
				$REX['ADDON']['installmsg'][$rxa_tinymce['name']] = 'Verzeichnis '.$source_dir.' konnte nicht nach '.$dest_dir.' kopiert werden!';
				$REX['ADDON']['install'][$rxa_tinymce['name']] = 0;
			}
		}
	}
