<?php

$mypage = 'compat';

$REX['INCLUDE_PATH'] = rex_path::src();
$REX['FRONTEND_PATH'] = rex_path::frontend();
$REX['MEDIAFOLDER']   = rex_path::media();
$REX['FRONTEND_FILE'] = 'index.php';

if($REX['REDAXO'])
{
  $I18N = new i18n($REX['LANG']);
}

require_once dirname(__FILE__) .'/functions/function_rex_file.inc.php';
require_once dirname(__FILE__) .'/functions/function_rex_file.inc.php';
require_once dirname(__FILE__) .'/functions/function_rex_other.inc.php';