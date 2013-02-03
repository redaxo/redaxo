<?php


/**
 *
 * @package redaxo5
 */

// Für größere Exports den Speicher für PHP erhöhen.
if (rex_ini_get('memory_limit') < 67108864) {
  @ini_set('memory_limit', '64M');
}

// ------- Addon Includes
include_once rex_path::addon('import_export', 'functions/function_import_export.php');
include_once rex_path::addon('import_export', 'functions/function_folder.php');
include_once rex_path::addon('import_export', 'functions/function_import_folder.php');


$subpage = rex_be_controller::getCurrentPagePart(2);

echo rex_view::title(rex_i18n::msg('im_export_importexport'));

if ($subpage == 'import' && rex::getUser()->hasPerm('import_export[import]'))
  require rex_path::addon('import_export', 'pages/import.php');
else
  require rex_path::addon('import_export', 'pages/export.php');
