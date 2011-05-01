<?php

$mypage = 'import_export';

if($REX['REDAXO'] && is_object($REX["USER"]))
{
  $REX['PERM'][] = 'import_export[export]';
  $REX['PERM'][] = 'import_export[import]';

  $pages = array();

  if($REX["USER"]->hasPerm('import_export[import]') || $REX["USER"]->isAdmin())
  {
  	$pages[] = array ('import', rex_i18n::msg('im_export_import'));
  }
  $pages[] = array ('', rex_i18n::msg('im_export_export'));
  $this->setProperty('pages', $pages);
}

if(rex_addon::exists('cronjob') && rex_addon::get('cronjob')->isAvailable())
{
  rex_cronjob_manager::registerType('rex_cronjob_export');
}