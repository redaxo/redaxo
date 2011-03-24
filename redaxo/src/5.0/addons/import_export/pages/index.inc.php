<?php


/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// Für größere Exports den Speicher für PHP erhöhen.
@ini_set('memory_limit', '64M');

// ------- Addon Includes
include_once rex_path::addon('import_export', 'functions/function_import_export.inc.php');
include_once rex_path::addon('import_export', 'functions/function_folder.inc.php');
include_once rex_path::addon('import_export', 'functions/function_import_folder.inc.php');
include_once rex_path::addon('import_export', 'functions/function_string.inc.php');


$subpage = rex_request('subpage', 'string');

rex_title(rex_i18n::msg("im_export_importexport"), $REX['ADDON']['pages']['import_export']);

if($subpage == "import" && ($REX["USER"]->hasPerm('import_export[import]') || $REX["USER"]->isAdmin()))
  require rex_path::addon('import_export', 'pages/import.inc.php');
else
  require rex_path::addon('import_export', 'pages/export.inc.php');