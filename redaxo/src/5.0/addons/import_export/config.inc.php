<?php

$mypage = 'import_export';

if($REX['REDAXO'] && is_object($REX["USER"]))
{
  $REX['ADDON']['rxid'][$mypage] = '1';

  $REX['PERM'][] = 'import_export[export]';
  $REX['PERM'][] = 'import_export[import]';

  $REX['ADDON']['pages'][$mypage] = array();

  if($REX["USER"]->hasPerm('import_export[import]') || $REX["USER"]->isAdmin())
  {
  	$REX['ADDON']['pages'][$mypage][] = array ('import', $REX['I18N']->msg('im_export_import'));
  }
  $REX['ADDON']['pages'][$mypage][] = array ('', $REX['I18N']->msg('im_export_export'));
}

if(rex_ooAddon::isAvailable('cronjob'))
{
  rex_cronjob_manager::registerType('rex_cronjob_export');
}