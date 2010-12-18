<?php


/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// Für größere Exports den Speicher für PHP erhöhen.
@ini_set('memory_limit', '64M');

// ------- Addon Includes
include_once $REX['SRC_PATH'] .'/addons/import_export/classes/class.tar.inc.php';
include_once $REX['SRC_PATH'] .'/addons/import_export/classes/class.rex_tar.inc.php';
include_once $REX['SRC_PATH'] .'/addons/import_export/functions/function_import_export.inc.php';
include_once $REX['SRC_PATH'] .'/addons/import_export/functions/function_folder.inc.php';
include_once $REX['SRC_PATH'] .'/addons/import_export/functions/function_import_folder.inc.php';
include_once $REX['SRC_PATH'] .'/addons/import_export/functions/function_string.inc.php';


$subpage = rex_request('subpage', 'string');


require $REX['SRC_PATH'] ."/core/layout/top.php";

rex_title($REX['I18N']->msg("im_export_importexport"), $REX['ADDON']['pages']['import_export']);

if($subpage == "import" && ($REX["USER"]->hasPerm('import_export[import]') || $REX["USER"]->isAdmin()))
  require $REX['SRC_PATH'] . '/addons/import_export/pages/import.inc.php';
else
  require $REX['SRC_PATH'] . '/addons/import_export/pages/export.inc.php';


require $REX['SRC_PATH'] ."/core/layout/bottom.php";