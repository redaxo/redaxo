<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'cronjob';

if($REX['REDAXO'])
{
  $EP = 'PAGE_CHECKED';
  
  if($REX['USER'] && rex_request('page', 'string') == 'be_dashboard')
  {
    rex_register_extension (
      'DASHBOARD_COMPONENT',
      array(new rex_cronjob_component(), 'registerAsExtension')
    );
  }
} else
{
  $EP = 'ADDONS_INCLUDED';
}

define('REX_CRONJOB_LOG_FOLDER', $REX['SRC_PATH'] .'/addons/cronjob/logs/');
define('REX_CRONJOB_TABLE'     , $REX['TABLE_PREFIX'] .'630_cronjobs');

// --- DYN
$REX['ADDON']['nexttime']['cronjob'] = "0";
// --- /DYN

if (isset($REX['ADDON']['nexttime'][$mypage]) 
  && $REX['ADDON']['nexttime'][$mypage] != 0 
  && time() >= $REX['ADDON']['nexttime'][$mypage])
{
  rex_register_extension($EP, 
    function ($params) 
    {
      global $REX;
      if (!$REX['REDAXO'] || !in_array($REX['PAGE'], array('setup', 'login', 'cronjob')))
      {
        rex_cronjob_manager_sql::factory()->check();
      }
    }
  );
}