<?php

/**
 * REDAXO Basis-Theme
 *
 * @author thomas[dot]blum[at]redaxo[dot]de Thomas Blum
 * @author <a href="http://www.blumbeet.com">www.blumbeet.com</a>
 *
 */

$addon = 'layout';
$plugin = 'base';


if($REX['REDAXO'])
{
	$markup = '<link rel="stylesheet" type="text/css" href="'. rex_path::pluginAssets($addon, $plugin, 'css_import.css', true) .'" media="screen, projection, print" />';
	rex_includeCss($markup);
	
	$class = 'layout-base';
	rex_addBodyClass($class);
}