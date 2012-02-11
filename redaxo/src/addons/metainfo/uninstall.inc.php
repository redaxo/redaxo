<?php

/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$this->setProperty('install', false);

$curDir = dirname(__FILE__);
require_once ($curDir .'/extensions/extension_cleanup.inc.php');

rex_metainfo_cleanup(array('force' => true));
