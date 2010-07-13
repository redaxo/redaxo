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

/*abstract*/ class rex_dashboard_notification extends rex_dashboard_component_base
{
  var $message;
  
  function rex_dashboard_notification($id, $cache_options = array())
  {
    if(!isset($cache_options['lifetime']))
    {
      // default cache lifetime in seconds
      $cache_options['lifetime'] = 60;
    }
    
    $this->message = '';
    parent::rex_dashboard_component_base($id, $cache_options);
  }
  
  /*public*/ function setMessage($message)
  {
    $this->message = $message;
  }
  
  /*public*/ function getMessage()
  {
    return $this->message;
  } 
  
  /*public*/ function _get()
  {
    $this->prepare();
    
    $message = $this->getMessage();
    
    if($message)
    {
      return $message;
    }
    return '';
  }
  
  /*
   * Static Method: Returns boolean if is notification
   */
  /*public static*/ function isValid($notification)
  {
    return is_object($notification) && is_a($notification, 'rex_dashboard_notification');
  }
}