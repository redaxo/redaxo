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
  /*private*/ var $message = '';
  
  /*public*/ function factory()
  {
    return new rex_cronjob_manager;
  }
  
  /*public*/ function setMessage($message)
  {
    $this->message = $message;
  }
  
  /*public*/ function getMessage()
  {
    return $this->message;
  }
  
  /*public*/ function hasMessage()
  {
    return !empty($this->message);
  }
  
  /*public*/ function check()
  {
    global $REX;
    $environment = (int)$REX['REDAXO'];
    $query = '
      SELECT    id, name, type, parameters, `interval`
      FROM      '. REX_CRONJOB_TABLE .' 
      WHERE     status=1 AND environment LIKE "%|'. $environment .'|%" AND nexttime <= '. time() .' 
      ORDER BY  nexttime ASC
      LIMIT     1
    ';
    $sql_manager = rex_cronjob_manager_sql::factory($this);
    $sql_manager->tryExecute($query);
  }
  
  /*public*/ function tryExecute(&$cronjob, $name = '', $params = array(), $log = true, $id = null)
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
  
  /*public*/ function saveNextTime($nexttime = null)
  {
    global $REX;
    if ($nexttime === null)
    {
      $sql_manager = rex_cronjob_manager_sql::factory($this);
      $nexttime = $sql_manager->getMinNextTime();
    }
    if ($nexttime === null)
      $nexttime = 0;
    else
      $nexttime = max(1, $nexttime);
    if ($nexttime != $REX['ADDON']['nexttime']['cronjob']) 
    {
      $content = '$REX[\'ADDON\'][\'nexttime\'][\'cronjob\'] = "'. addslashes($nexttime) .'";';
      $file = $REX['INCLUDE_PATH'] .'/addons/cronjob/config.inc.php';
      if (rex_replace_dynamic_contents($file, $content))
      {
        $REX['ADDON']['nexttime']['cronjob'] = $nexttime;
        return true;
      }
    }
    return false;
  }
  
  /*public static*/ function getTypes()
  {
    $types = array();
    $types[] = 'rex_cronjob_phpcode';
    $types[] = 'rex_cronjob_phpcallback';
    $types[] = 'rex_cronjob_urlrequest';
    
    // ----- EXTENSION POINT
    $types = rex_register_extension_point('CRONJOB_TYPES', $types);

    return $types;
  }
  
  /*public static*/ function registerExtension($params)
  {
    $params['subject'][] = $params['class'];
    return $params['subject'];
  }
}


class rex_cronjob_manager_sql
{
  /*private*/ var $sql;
  /*private*/ var $manager;
  
  /*private*/ function rex_cronjob_manager_sql(/*rex_cronjob_manager*/ $manager = null)
  {
    $this->sql = rex_sql::factory();
    // $this->sql->debugsql = true;
    if (is_a($manager, 'rex_cronjob_manager'))
      $this->manager = $manager;
    else
      $this->manager = rex_cronjob_manager::factory();
  }

  /*public*/ function factory(/*rex_cronjob_manager*/ $manager = null)
  {
    return new rex_cronjob_manager_sql($manager);
  }
  
  /*public*/ function setMessage($message)
  {
    $this->manager->setMessage($message);
  }
  
  /*public*/ function getMessage()
  {
    return $this->manager->getMessage();
  }
  
  /*public*/ function hasMessage()
  {
    return $this->manager->hasMessage();
  }

  /*public*/ function getName($id)
  {
    $this->sql->setQuery('
      SELECT  name 
      FROM    '. REX_CRONJOB_TABLE .' 
      WHERE   id='. $id .' 
      LIMIT   1
    ');
    if($this->sql->getRows() == 1)
      return $this->sql->getValue('name');
    return null;
  }

  /*public*/ function setStatus($id, $status)
  {
    global $REX;
    $this->sql->setTable(REX_CRONJOB_TABLE);
    $this->sql->setWhere('id = '. $id);
    $this->sql->setValue('status', $status);
    $this->sql->addGlobalUpdateFields();
    $success = $this->sql->update();
    $this->manager->saveNextTime($this->getMinNextTime());
    return $success;
  }
  
  /*public*/ function delete($id)
  {
    $this->sql->setTable(REX_CRONJOB_TABLE);
    $this->sql->setWhere('id = '. $id);
    $success = $this->sql->delete();
    $this->manager->saveNextTime($this->getMinNextTime());
    return $success;
  }
  
  /*public*/ function tryExecute($query_or_id, $log = true)
  {
    global $REX;
    if (is_int($query_or_id))
    {
      $environment = (int)$REX['REDAXO'];
      $this->sql->setQuery('
        SELECT    id, name, type, parameters, `interval` 
        FROM      '. REX_CRONJOB_TABLE .' 
        WHERE     id='. $query_or_id .' AND environment LIKE "%|'. $environment .'|%" 
        LIMIT     1
      ');
    }
    else
    {
      $this->sql->setQuery($query_or_id);
    }
    if ($this->sql->getRows() != 1)
    {
      $success = false;
      $this->manager->setMessage('Cronjob not found in database');
      $this->manager->saveNextTime($this->getMinNextTime());
    }
    else
    {
      $id       = $this->sql->getValue('id');
      $name     = $this->sql->getValue('name');
      $type     = $this->sql->getValue('type');
      $params   = unserialize($this->sql->getValue('parameters'));
      $interval = $this->sql->getValue('interval');

      $nexttime = $this->_calculateNextTime($interval);
      $this->setNextTime($id, $nexttime);

      $this->manager->saveNextTime($this->getMinNextTime());

      $cronjob = rex_cronjob::factory($type);
      $success = $this->manager->tryExecute($cronjob, $name, $params, $log, $id);
    }
    return $success;
  }
  
  /*public*/ function setNextTime($id, $nexttime)
  {
    return $this->sql->setQuery('
      UPDATE  '. REX_CRONJOB_TABLE .' 
      SET     nexttime='. $nexttime .' 
      WHERE   id='. $id
    );
  }
  
  /*public*/ function getMinNextTime()
  {
    $this->sql->setQuery('
      SELECT  MIN(nexttime) AS nexttime
      FROM    '. REX_CRONJOB_TABLE .' 
      WHERE   status=1
    ');
    if($this->sql->getRows() == 1)
      return $this->sql->getValue('nexttime');
    return null;
  }
  
  /*private*/ function _calculateNextTime($interval)
  {
    $interval = explode('|', trim($interval, '|'));
    if (is_array($interval) && isset($interval[0]) && isset($interval[1]))
    {
      $date = getdate();
      switch($interval[1])
      {
        case 'h': return mktime($date['hours'] + $interval[0], 0, 0);
        case 'd': return mktime(0, 0, 0, $date['mon'], $date['mday'] + $interval[0]);
        case 'w': return mktime(0, 0, 0, $date['mon'], $date['mday'] + $interval[0] * 7 - $date['wday']);
        case 'm': return mktime(0, 0, 0, $date['mon'] + $interval[0], 1);
        case 'y': return mktime(0, 0, 0, 1, 1, $date['year'] + $interval[0]);
      } 
    }
    return null;
  }
}