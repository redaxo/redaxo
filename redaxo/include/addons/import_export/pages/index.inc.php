<?php


/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// Für größere Exports den Speicher für PHP erhöhen.
@ini_set('memory_limit', '64M');

// ------- Addon Includes
include_once $REX['INCLUDE_PATH'].'/addons/import_export/classes/class.tar.inc.php';
include_once $REX['INCLUDE_PATH'].'/addons/import_export/classes/class.rex_tar.inc.php';
include_once $REX['INCLUDE_PATH'].'/addons/import_export/functions/function_import_export.inc.php';
include_once $REX['INCLUDE_PATH'].'/addons/import_export/functions/function_folder.inc.php';
include_once $REX['INCLUDE_PATH'].'/addons/import_export/functions/function_import_folder.inc.php';
include_once $REX['INCLUDE_PATH'].'/addons/import_export/functions/function_string.inc.php';


$subpage = rex_request('subpage', 'string');


require $REX['INCLUDE_PATH']."/layout/top.php";

rex_title($I18N->msg("im_export_importexport"), $REX['ADDON']['pages']['import_export']);

if($subpage == "import" && ($REX["USER"]->hasPerm('import_export[import]') || $REX["USER"]->isAdmin()))
  require $REX['INCLUDE_PATH'] . '/addons/import_export/pages/import.inc.php';
else
  require $REX['INCLUDE_PATH'] . '/addons/import_export/pages/export.inc.php';


require $REX['INCLUDE_PATH']."/layout/bottom.php";