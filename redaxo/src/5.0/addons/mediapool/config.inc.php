<?php

/**
 * Mediapool Addon
 *
 * @author jan[dot]kristinus[at]redaxo[dot]de Jan Kristinus
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$mypage = 'mediapool';

if($REX["REDAXO"])
{
  $mpool = new rex_be_page_popup($REX['I18N']->msg('mediapool'), 'openMediaPool(); return false;');
  $mpool->setRequiredPermissions('hasMediaPerm');
  $REX['ADDON']['page'][$mypage] = $mpool; 
  
  require_once dirname(__FILE__). '/functions/function_rex_mediapool.inc.php';
  // im backend und eingeloggt?
  if($REX["USER"])
  {
    rex_register_extension('PAGE_HEADER', 'rex_mediapool_add_assets');
  }
}

require_once dirname(__FILE__). '/functions/function_rex_generate.inc.php';

$REX['VARIABLES'][] = 'rex_var_media';