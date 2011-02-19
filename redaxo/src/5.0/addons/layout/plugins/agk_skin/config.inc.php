<?php

/**
 * REDAXO Theme
 *
 * @author Design
 * @author ralph.zumkeller[at]yakamara[dot]de Ralph Zumkeller
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 *
 * @author Umsetzung
 * @author thomas[dot]blum[at]redaxo[dot]de Thomas Blum
 * @author <a href="http://www.blumbeet.com">www.blumbeet.com</a>
 *
 */

$addon = 'layout';
$plugin = 'agk_skin';

if($REX['REDAXO'])
{
	$markup = '<link rel="stylesheet" type="text/css" href="'. rex_path::pluginAssets($addon, $plugin, 'css_import.css', true) .'" media="screen, projection, print" />';
	rex_includeCss($markup);
	
	$class = 'layout-agk-skin';
	rex_addBodyClass($class);
}