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

if(rex_core::isBackend())
{
  $EP = 'PAGE_CHECKED';

  if(rex_core::getUser() && rex_request('page', 'string') == 'be_dashboard')
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
define('REX_CRONJOB_TABLE'     , rex_core::getTablePrefix() .'cronjob');

rex_extension::register('ADDONS_INCLUDED',
  function($params)
  {
    foreach(rex_addon::get('cronjob')->getAvailablePlugins() as $plugin)
    {
      if(($type = $plugin->getProperty('cronjob_type')) != '')
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
      if (!rex_core::isBackend() || !in_array(rex_core::getProperty('page'), array('setup', 'login', 'cronjob')))
      {
        rex_cronjob_manager_sql::factory()->check();
      }
    }
  );
}