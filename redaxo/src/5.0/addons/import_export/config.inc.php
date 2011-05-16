<?php

$mypage = 'import_export';

if(rex::isBackend() && is_object(rex::getUser()))
{
  $REX['PERM'][] = 'import_export[export]';
  $REX['PERM'][] = 'import_export[import]';

  $pages = array();

  if(rex::getUser()->hasPerm('import_export[import]') || rex::getUser()->isAdmin())
  {
  	$pages[] = array ('import', rex_i18n::msg('im_export_import'));
  }
  $pages[] = array ('', rex_i18n::msg('im_export_export'));
  $this->setProperty('pages', $pages);
}

if(rex_addon::get('cronjob')->isAvailable())
{
  rex_cronjob_manager::registerType('rex_cronjob_export');
}