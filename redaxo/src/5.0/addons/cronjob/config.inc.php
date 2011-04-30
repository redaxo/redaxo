<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
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

define('REX_CRONJOB_LOG_FOLDER', rex_path::addonData($mypage));
define('REX_CRONJOB_TABLE'     , $REX['TABLE_PREFIX'] .'cronjob');

rex_extension::register('ADDONS_INCLUDED',
  function($params)
  {
    foreach(rex_ooPlugin::getAvailablePlugins('cronjob') as $plugin)
    {
      if(($type = rex_ooPlugin::getProperty('cronjob', $plugin, 'cronjob_type')) != '')
      {
        rex_cronjob_manager::registerType($type);
      }
    }
  }
);

$nexttime = rex_config::get($mypage, 'nexttime');

if ($nexttime != 0 && time() >= $nexttime)
{
  rex_extension::register($EP,
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