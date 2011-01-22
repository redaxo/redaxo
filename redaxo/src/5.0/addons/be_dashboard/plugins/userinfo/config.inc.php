<?php

/**
 * Userinfo Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'userinfo';

// im backend und eingeloggt?
if($REX["REDAXO"] && $REX["USER"])
{
  if(rex_request('page', 'string') == 'be_dashboard')
  {
    if(!defined('A659_DEFAULT_LIMIT'))
    {
      define('A659_DEFAULT_LIMIT', 7);
    }

    require_once dirname(__FILE__) .'/functions/function_userinfo.inc.php';

    $components = array(
      'rex_articles_component',
      'rex_media_component',
      'rex_stats_component',
    );

    foreach($components as $compClass)
    {
      rex_register_extension (
        'DASHBOARD_COMPONENT',
        array(new $compClass(), 'registerAsExtension')
      );
    }
  }
}
