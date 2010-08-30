<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_cronjob_manager_sql
{
  private 
    $sql,
    $manager;
  
  private function rex_cronjob_manager_sql(rex_cronjob_manager $manager = null)
  {
    $this->sql = rex_sql::factory();
    // $this->sql->debugsql = true;
    $this->manager = $manager;
  }

  public function factory(rex_cronjob_manager $manager = null)
  {
    return new rex_cronjob_manager_sql($manager);
  }

  public function getManager()
  {
    if (!is_object($this->manager))
    {
      $this->manager = rex_cronjob_manager::factory();
    }
    return $this->manager;
  }
  
  public function setMessage($message)
  {
    $this->getManager()->setMessage($message);
  }
  
  public function getMessage()
  {
    return $this->getManager()->getMessage();
  }
  
  public function hasMessage()
  {
    return $this->getManager()->hasMessage();
  }
  
  public function check()
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
    $this->tryExecute($query);
  }

  public function getName($id)
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

  public function setStatus($id, $status)
  {
    global $REX;
    $this->sql->setTable(REX_CRONJOB_TABLE);
    $this->sql->setWhere('id = '. $id);
    $this->sql->setValue('status', $status);
    $this->sql->addGlobalUpdateFields();
    $success = $this->sql->update();
    $this->saveNextTime();
    return $success;
  }
  
  public function delete($id)
  {
    $this->sql->setTable(REX_CRONJOB_TABLE);
    $this->sql->setWhere('id = '. $id);
    $success = $this->sql->delete();
    $this->saveNextTime();
    return $success;
  }
  
  public function tryExecute($query_or_id, $log = true)
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
      $this->getManager()->setMessage('Cronjob not found in database');
      $this->saveNextTime();
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

      $this->saveNextTime();

      $cronjob = rex_cronjob::factory($type);
      $success = $this->getManager()->tryExecute($cronjob, $name, $params, $log, $id);
    }
    return $success;
  }
  
  public function setNextTime($id, $nexttime)
  {
    return $this->sql->setQuery('
      UPDATE  '. REX_CRONJOB_TABLE .' 
      SET     nexttime='. $nexttime .' 
      WHERE   id='. $id
    );
  }
  
  public function getMinNextTime()
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
  
  public function saveNextTime($nexttime = null)
  {
    global $REX;
    if ($nexttime === null)
    {
      $nexttime = $this->getMinNextTime();
    }
    if ($nexttime === null)
      $nexttime = 0;
    else
      $nexttime = max(1, $nexttime);
    if ($nexttime != $REX['ADDON']['nexttime']['cronjob']) 
    {
      $content = '$REX[\'ADDON\'][\'nexttime\'][\'cronjob\'] = "'. addslashes($nexttime) .'";';
      $file = $REX['SRC_PATH'] .'/addons/cronjob/config.inc.php';
      if (rex_replace_dynamic_contents($file, $content))
      {
        $REX['ADDON']['nexttime']['cronjob'] = $nexttime;
        return true;
      }
    }
    return false;
  }
  
  private function _calculateNextTime($interval)
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