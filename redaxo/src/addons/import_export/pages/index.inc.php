<?php


/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// Für größere Exports den Speicher für PHP erhöhen.
if(rex_ini_get('memory_limit') < 67108864)
{
  @ini_set('memory_limit', '64M');
}

// ------- Addon Includes
include_once rex_path::addon('import_export', 'functions/function_import_export.inc.php');
include_once rex_path::addon('import_export', 'functions/function_folder.inc.php');
include_once rex_path::addon('import_export', 'functions/function_import_folder.inc.php');


$subpage = rex_request('subpage', 'string');

echo rex_view::title(rex_i18n::msg("im_export_importexport"));

if($subpage == "import" && (rex::getUser()->hasPerm('import_export[import]') || rex::getUser()->isAdmin()))
  require rex_path::addon('import_export', 'pages/import.inc.php');
else
  require rex_path::addon('import_export', 'pages/export.inc.php');