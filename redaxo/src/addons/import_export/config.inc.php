<?php

$mypage = 'import_export';

if (rex::isBackend() && is_object(rex::getUser()))
{
  rex_perm::register('import_export[export]');
  rex_perm::register('import_export[import]');

  $pages = array();

  if (rex::getUser()->hasPerm('import_export[import]'))
  {
    $pages[] = array ('import', rex_i18n::msg('im_export_import'));
  }
  $pages[] = array ('', rex_i18n::msg('im_export_export'));
  $this->setProperty('pages', $pages);
}

if (rex_addon::get('cronjob')->isAvailable())
{
  rex_cronjob_manager::registerType('rex_cronjob_export');
}
