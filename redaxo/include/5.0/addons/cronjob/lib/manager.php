<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_cronjob_manager
{
  static private
    $types = array(
      'rex_cronjob_phpcode', 
      'rex_cronjob_phpcallback', 
      'rex_cronjob_urlrequest'
    );

  private 
    $message = '';
  
  public function factory()
  {
    return new rex_cronjob_manager;
  }
  
  public function setMessage($message)
  {
    $this->message = $message;
  }
  
  public function getMessage()
  {
    return $this->message;
  }
  
  public function hasMessage()
  {
    return !empty($this->message);
  }
  
  public function tryExecute($cronjob, $name = '', $params = array(), $log = true, $id = null)
  {
    global $REX;
    
    $message = '';
    $success = rex_cronjob::isValid($cronjob);
    if(!$success)
    {
      if (is_object($cronjob))
        $message = 'Invalid cronjob class "'. get_class($cronjob) .'"';
      else
        $message = 'Class "'. $cronjob .'" not found';
    }
    else
    {
      $type = $cronjob->getType();
      if (is_array($params))
      {
        foreach($params as $key => $value)
          $cronjob->setParam(str_replace($type.'_', '', $key), $value);
      }
      $success = $cronjob->execute();
      $message = $cronjob->getMessage();
      if ($message == '' && !$success)
      {
        $message = 'Unknown error';
      }
      if($log && !$name)
      {
        if($REX['REDAXO'])
          $name = $cronjob->getTypeName();
        else
          $name = $type;
      }
    }
    
    if ($log) 
    {
      if (!$name)
        $name = '[no name]';
      rex_cronjob_log::save($name, $success, $message, $id);
    }
    
    $this->setMessage(htmlspecialchars($message));
    
    return $success;
  }
  
  static public function getTypes()
  {
    $types = self::$types;

    // ----- EXTENSION POINT - DEPRECATED
    $types = rex_register_extension_point('CRONJOB_TYPES', $types);

    return $types;
  }
  
  static public function registerType($class)
  {
    self::$types[] = $class;
  }
  
  // DEPRECATED
  static public function registerExtension($params)
  {
    $params['subject'][] = $params['class'];
    return $params['subject'];
  }

  // DEPRECATED
  public function check()
  {
    $sql_manager = rex_cronjob_manager_sql::factory($this);
    $sql_manager->check();
  }

  // DEPRECATED
  public function saveNextTime($nexttime = null)
  {
    $sql_manager = rex_cronjob_manager_sql::factory($this);
    return $sql_manager->saveNextTime($nexttime);
  }
}