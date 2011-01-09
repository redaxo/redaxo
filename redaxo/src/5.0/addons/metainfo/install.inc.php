<?php

/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';

$curDir = dirname(__FILE__);
require_once ($curDir .'/extensions/extension_cleanup.inc.php');
rex_a62_metainfo_cleanup(array('force' => true));

// uninstall ausführen, damit die db clean ist vorm neuen install
$uninstall = $curDir.'/uninstall.sql';
rex_install_dump($uninstall);

// TODO:
// - Update von alten Version einfliessen lassen

if ($error != '')
  $REX['ADDON']['installmsg']['metainfo'] = $error;
else
  $REX['ADDON']['install']['metainfo'] = true;