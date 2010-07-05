<?php

/**
 * Image-Resize Addon
 *
 * @author office[at]vscope[dot]at Wolfgang Hutteger
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]yakmara[dot]de Jan Kristinus
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');
$msg = '';

require $REX['INCLUDE_PATH'] . '/layout/top.php';

if ($subpage == 'clear_cache')
{
  $c = rex_thumbnail::deleteCache();
  $msg = $I18N->msg('iresize_cache_files_removed', $c);
}

rex_title('Image Resize', $REX['ADDON'][$page]['SUBPAGES']);

// Include Current Page
switch($subpage)
{
  case 'settings' :
  {
    break;
  }

  default:
  {
  	if ($msg != '')
		  echo rex_info($msg);

	  $subpage = 'overview';
  }
}

require $REX['INCLUDE_PATH'] . '/addons/image_resize/pages/'.$subpage.'.inc.php';
require $REX['INCLUDE_PATH'] . '/layout/bottom.php';