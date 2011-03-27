<?php

/**
 * Backenddashboard Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_notification_component extends rex_dashboard_component
{
  public function __construct()
  {
    global $REX;

    parent::__construct('notifications');
    $this->setTitle(rex_i18n::msg('dashboard_notifications'));
    $this->setFormat('full');
  }

  protected function prepare()
  {
    // ----- EXTENSION POINT
    $dashboard_notifications = array();
    $dashboard_notifications = rex_register_extension_point('DASHBOARD_NOTIFICATION', $dashboard_notifications);

    $content = '';
    if(count($dashboard_notifications) > 0)
    {
      foreach($dashboard_notifications as $notification)
      {
        if(rex_dashboard_notification::isValid($notification) && $notification->checkPermission())
        {
          $content .= $notification->_get();
        }
      }
      unset($dashboard_notifications);
    }

    $this->setContent($content);
  }
}