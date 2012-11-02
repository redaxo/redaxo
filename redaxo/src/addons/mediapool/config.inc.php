<?php

/**
 * Mediapool Addon
 *
 * @author jan[dot]kristinus[at]redaxo[dot]de Jan Kristinus
 *
 * @package redaxo5
 */

$mypage = 'mediapool';

rex_complex_perm::register('media', 'rex_media_perm');

if (rex::isBackend() && rex::getUser()) {
  $mpool = new rex_be_page_popup('mediapool', rex_i18n::msg('mediapool'), 'openMediaPool(); return false;');
  $mpool->setRequiredPermissions('media/hasMediaPerm');
  $mpool->addSubPage(new rex_be_page('', rex_i18n::msg('pool_file_list')));
  $mpool->addSubPage(new rex_be_page('upload', rex_i18n::msg('pool_file_insert')));
  if (rex::getUser()->getComplexPerm('media')->hasCategoryPerm(0)) {
    $mpool->addSubPage(new rex_be_page('structure', rex_i18n::msg('pool_cat_list')));
    $mpool->addSubPage(new rex_be_page('sync', rex_i18n::msg('pool_sync_files')));
  }
  $mainPage = new rex_be_page_main('system', $mpool);
  $mainPage->setPrio(20);
  $this->setProperty('page', $mainPage);

  require_once dirname(__FILE__) . '/functions/function_rex_mediapool.inc.php';

  rex_extension::register('PAGE_HEADER', 'rex_mediapool_add_assets');
}
