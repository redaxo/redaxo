<?php

/*
 * - rex_config verwenden, webservice nur auf anfrage laden
 * -
 *
 */

$registered_addons = rex_ooAddon::getRegisteredAddons();
$addons = rex_install::getAddOns();

$show_list = TRUE;

$addon_key = rex_request('addon_key','string');


if(array_key_exists($addon_key,$addons))
{

	$func = rex_request("func","string");
	$msg = rex_request("msg","string");
	$addon = $addons[$addon_key];
	rex_addonManager::loadPackage($addon_key);

	echo rex_info(htmlspecialchars(stripslashes($msg)));


	if($func == "install")
	{

		// func
		// - prüfen ob für aktuelle Version geeignet
		// - prüfen ob zip datei richtiges format und keine datei ausserhalb des erlaubten
		// - schreib rechte vorhanden ?
		// - leserechte vorhanden ?
		// - abhaengigkeiten von anderen addon zu diesem addon prüfen
		// -- andere addons erst vom user deaktiveren lassen
		// - erklärung was man machen muss wenn es schief gegangen ist...


		// download - alte version sichern - neues drüberbügeln - update.php ausführen
		// downloads - keine alte version ist vorhanden

		$msg = 'AddOn wurde heruntergeladen und aufgepsielt';
		$msg.= 'das alte AddOn wurde überschrieben und hier ... gesichert.';

		ob_end_clean();
		header('Location: index.php?page=install&subpage=addons&addon_key='.$addon_key.'&msg='.urlencode($msg));
		exit;






	}






	echo '<h2>Allgemeine AddOn Informationen</h2>';

	echo '<table class="rex_table">';

	echo '<tbody>';
	echo '<tr><th>AddOnName</th><td>'.htmlspecialchars($addon['addon_name']).'</td></tr>';
	echo '<tr><th>AddOn-Key</th><td>'.htmlspecialchars($addon['addon_key']).'</td></tr>';
	/* unimportant: addon_created, addon_updated */
	echo '<tr><th>Kurzbeschreibung</th><td>'.nl2br(htmlspecialchars($addon['addon_shortdescription'])).'</td></tr>';
	echo '<tr><th>Beschreibung</th><td>'.nl2br(htmlspecialchars($addon['addon_description'])).'</td></tr>';
	echo '<tr><th>Mehr Infos</th><td><a href="http://www.redaxo.org/de/download/addons/?addon_id='.urlencode($addon['addon_id']).'">'.$addon['addon_name'].'</a></td></tr>';
	echo '</tbody>';

	echo '</table>';

	echo '<h2>Versionsinformationen</h2>';

	echo '<table class="rex_table">';

	echo '<tbody>';
	echo '<tr><th>Verfügbare Version</th><td>'.htmlspecialchars($addon['file_version']).'</td></tr>';
	$current_version = rex_ooAddon::getVersion($addon_key);
	if(!in_array($addon_key,$registered_addons)){ $current_version = 'AddOn ist nicht vorhanden.';
	}elseif($current_version == "") { $current_version = 'AddOn ist vorhanden aber Version ist nicht auslesbar'; }
	echo '<tr><th>Einsetzte Version</th><td>'.htmlspecialchars($current_version).'</td></tr>';
	echo '<tr><th>Veröffentlichung</th><td>'.nl2br(htmlspecialchars($addon['file_updated'])).'</td></tr>';
	echo '<tr><th>Beschreibung</th><td>'.nl2br(htmlspecialchars($addon['file_description'])).'</td></tr>';
	echo '<tr><th>Direkter Downloadlink für manuelle Installation</th><td><a href="'.htmlspecialchars($addon['file_path']).'">'.htmlspecialchars($addon['file_name']).'</a></td></tr>';
	echo '</tbody>';

	echo '</table>';

	$submit = 'AddOn herunterladen und einspielen.';
	$current_version = rex_ooAddon::getVersion($addon_key);
	if(!in_array($addon_key,$registered_addons))
	{
		echo rex_info('AddOn ist bisher nicht vorhanden und kann installiert werden.');

	}elseif($current_version == "") {

		echo rex_info('AddOn ist vorhanden aber Version ist nicht auslesbar. Die Folgen bei einer Aktualisierung sind unklar.');

	}elseif (version_compare( $current_version , $addon['file_version']) == -1)
	{
		echo rex_info('AddOn ist vorhanden und neuere Version verfügbar. Es wird eine Aktualisierung empfohlen.');

	}elseif (version_compare( $current_version , $addon['file_version']) == 0)
	{
		echo rex_warning('Die Versionen der vorhandenen und der online Version sind identisch. Daher ist keine Aktualisierung nötig.');

	}else
	{
		echo rex_warning('Die vorhandene Version des AddOns ist neuer als die verfügbare Version. Da dies nicht vorkommen sollte, empfehlen wir keine Aktualisierung.');

	}

	echo '<p><a href="index.php?page=install&subpage=addons&addon_key='.$addon_key.'&func=install">'.$submit.'</a></p>';

	echo '<p><a href="index.php?page=install&subpage=addons">Zurück zur Übersicht</a></p>';

	$show_list = FALSE;

}



// *********************************** list of addons

if($show_list)
{

	echo '<table class="rex-table">';

	echo '<thead>';
	echo '<tr>';
	echo '  <th></th>';
	echo '  <th>AddOnName [Key]</th>';
	echo '  <th>Beschreibung</th>';
	echo '  <th>Status</th>';
	echo '</thead>';

	echo '<tbody>';
	foreach($addons as $addon => $v)
	{
		rex_addonManager::loadPackage($addon);

		echo '<tr>';
		echo '<td class="rex-icon rex-col-a"><span class="rex-i-element rex-i-addon"><span class="rex-i-element-in">be_dashboard</span></span></td>';

		echo '<td><a href="index.php?page=install&subpage=addons&addon_key='.urlencode($addon).'">'.htmlspecialchars($v['addon_name']).'</a> ['.$addon.']</td>';
		echo '<td>'.htmlspecialchars(substr($v['addon_shortdescription'],0,100)).' ...</td>';

		$status = '';
		if(!in_array($addon,$registered_addons))
		{
			$status = 'Addon ist nicht vorhanden';

		}else
		{
			$version = rex_ooAddon::getVersion($addon);
			if($version == "") {
				$status = 'Keine Version auslesbar';

			}else
			{
				if (version_compare( $version , $v['file_version']) == -1)
				{
					$status = 'Aktuellere Version vorhanden<br /><br />Aktuell: '.$version.'<br />Verfügbar: '.$v['file_version'];

				}else
				{
					$status = 'Keine neuere Version verfügbar';

				}

			}

		}

		echo '<td>'.$status.'</td>';
		echo '</tr>';

	}
	echo '<tbody>';

	echo '</table>';

}



?>