<?php

/**
 * Backenddashboard Addon
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'be_dashboard';

/* Addon Parameter */
$REX['ADDON']['rxid'][$mypage] = '655';
$REX['ADDON']['name'][$mypage] = 'Dashboard';
$REX['ADDON']['perm'][$mypage] = 'be_dashboard[]';
$REX['ADDON']['version'][$mypage] = '1.3';
$REX['ADDON']['navigation'][$mypage] = array('block'=>'system');
$REX['ADDON']['author'][$mypage] = 'Markus Staab';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';

$REX['PERM'][] = 'be_dashboard[]';

// im backend und eingeloggt?
if($REX["REDAXO"] && $REX["USER"])
{
  if(rex_request('page', 'string') == 'be_dashboard')
  {
    $I18N->appendFile(dirname(__FILE__). '/lang/');
      
    require_once dirname(__FILE__) .'/classes/class.rex_cache.inc.php';
    require_once dirname(__FILE__) .'/classes/cache/class.rex_cache_file.inc.php';
    require_once dirname(__FILE__) .'/classes/cache/class.rex_cache_function.inc.php';
    require_once dirname(__FILE__) .'/classes/class.component_base.inc.php';
    require_once dirname(__FILE__) .'/classes/class.component_config.inc.php';
    require_once dirname(__FILE__) .'/classes/class.component.inc.php';
    require_once dirname(__FILE__) .'/classes/class.notification.inc.php';
    require_once dirname(__FILE__) .'/classes/class.notification_component.inc.php';
    
    require_once dirname(__FILE__) .'/functions/function_dashboard.inc.php';
    rex_register_extension('PAGE_HEADER', 'rex_a655_add_assets');
  }
}
