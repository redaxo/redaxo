<?php

/**
 *
 * @author blumbeet - web.studio
 * @author Thomas Blum
 * @author mail[at]blumbeet[dot]com Thomas Blum
 *
 */



// Einstellungen ---------------------------------------------------------------
$basedir = __DIR__;
$myaddon = 'watson';

// Check AddOns und Versionen --------------------------------------------------
require_once($basedir . '/lib/' . $myaddon . '_load.php');
$myclass = $myaddon . '_load';
$myclass::install();
