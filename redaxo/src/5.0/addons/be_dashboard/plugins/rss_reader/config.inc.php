<?php

/**
 * RSS Reader Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'rss_reader';

/* Addon Parameter */
$REX['ADDON']['rxid'][$mypage] = '656';
$REX['ADDON']['version'][$mypage] = '1.3';
$REX['ADDON']['author'][$mypage] = 'Markus Staab';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';

// im backend und eingeloggt?
if($REX["REDAXO"] && $REX["USER"])
{
  if(rex_request('page', 'string') == 'be_dashboard')
  {
    require_once dirname(__FILE__) .'/functions/function_reader.inc.php';

    rex_register_extension(
      'DASHBOARD_COMPONENT',
      array(new rex_rss_reader_component(), 'registerAsExtension')
    );
  }
}
