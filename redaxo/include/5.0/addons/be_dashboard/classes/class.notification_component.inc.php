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

/*abstract*/ class rex_notification_component extends rex_dashboard_component
{
  function rex_notification_component()
  {
    global $I18N;
    
    parent::rex_dashboard_component_base('notifications');
    $this->setTitle($I18N->msg('dashboard_notifications'));
    $this->setFormat('full');
  }
  
  /*protected*/ function prepare()
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