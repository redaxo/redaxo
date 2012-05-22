<?php

/**
 * Image-Resize Addon
 *
 * @author office[at]vscope[dot]at Wolfgang Hutteger
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]yakmara[dot]de Jan Kristinus
 * @author dh[at]daveholloway[dot]co[dot]uk Dave Holloway
 *
 * @package redaxo5
 */

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');
$msg = '';

if ($subpage == 'clear_cache')
{
  $c = rex_media_manager::deleteCache();
  $msg = rex_i18n::msg('media_manager_cache_files_removed', $c);
}

echo rex_view::title('Media Manager');

// Include Current Page
switch ($subpage)
{
  case 'types' :
  case 'effects' :
  case 'settings' :
    break;

  default:
  {
    if ($msg != '')
      echo rex_view::info($msg);

    $subpage = 'overview';
  }
}

require dirname(__FILE__) . '/' . $subpage . '.inc.php';
