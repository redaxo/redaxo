<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

if (rex::isBackend())
{
  $EP = 'PAGE_CHECKED';

  if (rex::getUser() && rex_request('page', 'string') == 'be_dashboard')
  {
    rex_extension::register(
      'DASHBOARD_COMPONENT',
      array(new rex_cronjob_component(), 'registerAsExtension')
    );
  }
}
else
{
  $EP = 'ADDONS_INCLUDED';
}

define('REX_CRONJOB_LOG_FOLDER', $this->getDataPath());
define('REX_CRONJOB_TABLE'     , rex::getTable('cronjob'));

rex_extension::register('ADDONS_INCLUDED',
  function($params)
  {
    foreach (rex_addon::get('cronjob')->getAvailablePlugins() as $plugin)
    {
      if (($type = $plugin->getProperty('cronjob_type')) != '')
      {
        rex_cronjob_manager::registerType($type);
      }
    }
  }
);

$nexttime = $this->getConfig('nexttime', 0);

if ($nexttime != 0 && time() >= $nexttime)
{
  rex_extension::register($EP,
    function ($params)
    {
      if (!rex::isBackend() || !in_array(rex::getProperty('page'), array('setup', 'login', 'cronjob')))
      {
        rex_cronjob_manager_sql::factory()->check();
      }
    }
  );
}
