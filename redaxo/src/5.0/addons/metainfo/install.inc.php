<?php

/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$error = '';

$curDir = dirname(__FILE__);
require_once ($curDir .'/extensions/extension_cleanup.inc.php');
rex_a62_metainfo_cleanup(array('force' => true));

// uninstall ausführen, damit die db clean ist vorm neuen install
$uninstall = $curDir.'/uninstall.sql';
rex_sql_dump::import($uninstall);

// check wheter the columns inside the core have already been installed
$coreAlreadyUpdated = false;
$articleColumns = rex_sql::showColumns(rex_core::getTablePrefix(). 'article');
foreach($articleColumns as $column)
{
  if($column['name'] == 'art_online_from')
  {
    $coreAlreadyUpdated = true;
    break;
  }
}

if(!$coreAlreadyUpdated)
{
  $coreinstall = $curDir.'/coreinstall.sql';
  rex_sql_dump::import($coreinstall);
}

// TODO:
// - Update von alten Version einfliessen lassen

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);